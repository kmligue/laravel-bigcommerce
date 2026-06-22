import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import api from '../../api/client';
import type { AdminInstallsData, AdminStore } from '../../types';
import { formatDate } from '../../hooks/useStoreContext';
import PageTitle from '../../components/PageTitle';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function AdminInstalls() {
  const [data, setData] = useState<AdminInstallsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [editingPlan, setEditingPlan] = useState<string | null>(null);
  const [editingTrial, setEditingTrial] = useState<string | null>(null);
  const [planSelections, setPlanSelections] = useState<Record<string, string>>({});
  const [trialDates, setTrialDates] = useState<Record<string, string>>({});

  const loadData = () => {
    api
      .get<AdminInstallsData>('/api/limonadmin/installs')
      .then((res) => setData(res.data))
      .catch(() => toast.error('Failed to load installs.'))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handlePlanChange = async (store: AdminStore) => {
    const plan = planSelections[store.store_hash] ?? '';
    const storeHashShort = store.store_hash_short;

    try {
      const res = await api.post(`/api/stores/${storeHashShort}/billing/${plan}/select`);
      if (res.data.success) {
        if (res.data.url) {
          toast.error('Customer needs to enter their card details to proceed');
        } else {
          toast.success(res.data.message ?? 'Plan updated successfully');
          setTimeout(loadData, 1500);
        }
      } else {
        toast.error(res.data.message ?? 'Failed to update plan');
      }
    } catch {
      toast.error('Failed to update plan');
    }
  };

  const handleTrialChange = async (store: AdminStore) => {
    const trial = trialDates[store.store_hash] ?? '';
    const storeHashShort = store.store_hash_short;

    try {
      const res = await api.post(`/api/stores/${storeHashShort}/billing/trial/change`, { trial });
      if (res.data.success) {
        toast.success(res.data.message ?? 'Trial updated successfully');
        setTimeout(loadData, 1500);
      } else {
        toast.error(res.data.message ?? 'Failed to update trial');
      }
    } catch {
      toast.error('Failed to update trial');
    }
  };

  if (loading || !data) {
    return <LoadingSpinner />;
  }

  return (
    <>
      <PageTitle title={`Limon Admin / ${data.app_name} / Installs`} />

      <div className="bg-white shadow-md p-5 mt-8 overflow-x-auto">
        <table className="border-collapse table-auto w-full text-sm">
          <thead>
            <tr>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Store Hash</th>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Store Name</th>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Name</th>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Plan</th>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Install Date</th>
              <th className="border-b border-[#9a9da1] p-4 pl-8 pt-0 pb-3 text-left">Expiration Date</th>
            </tr>
          </thead>
          <tbody className="bg-white">
            {data.stores.map((store) => (
              <tr key={store.store_hash}>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                  <details className="cursor-pointer">
                    <summary>
                      <a href={store.load_url} className="hover:underline">
                        {store.store_hash_short}
                      </a>
                    </summary>
                    <ul className="mt-2 text-xs space-y-1">
                      {Object.entries(store.metadata).map(([key, value]) => (
                        <li key={key}>
                          {key}: {String(value)}
                        </li>
                      ))}
                    </ul>
                  </details>
                </td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{store.name}</td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500 w-1/6">
                  <div className="flex items-center gap-1">
                    <a href={`mailto:${store.user_email}`}>
                      <svg className="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                      </svg>
                    </a>
                    <span>{store.user_name}</span>
                  </div>
                </td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                  {editingPlan !== store.store_hash ? (
                    <div className="flex items-center gap-2">
                      <span>
                        {store.plan_label}
                        {store.plan_price && store.plan_price > 0 ? ` ($${store.plan_price.toFixed(2)}/month)` : ''}
                      </span>
                      <button
                        type="button"
                        className="text-xs underline"
                        onClick={() => {
                          setEditingPlan(store.store_hash);
                          setPlanSelections((prev) => ({
                            ...prev,
                            [store.store_hash]: store.plan_options.find((p) => p.plan_id === store.plan_id)?.key ?? '',
                          }));
                        }}
                      >
                        Edit
                      </button>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      <select
                        className="border plan"
                        value={planSelections[store.store_hash] ?? ''}
                        onChange={(e) =>
                          setPlanSelections((prev) => ({ ...prev, [store.store_hash]: e.target.value }))
                        }
                      >
                        <option value="">-</option>
                        {store.plan_options.map((opt) => (
                          <option key={opt.key} value={opt.key}>
                            {opt.label}
                          </option>
                        ))}
                      </select>
                      <button
                        type="button"
                        className="border px-1"
                        onClick={() => handlePlanChange(store)}
                      >
                        Change
                      </button>
                      <button type="button" onClick={() => setEditingPlan(null)}>
                        Cancel
                      </button>
                    </div>
                  )}
                </td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                  {store.install_date ? formatDate(store.install_date) : '-'}
                </td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">
                  {editingTrial !== store.store_hash ? (
                    <div className="flex items-center gap-2">
                      <span>{store.trial_ends_at ? formatDate(store.trial_ends_at) : 'N/A'}</span>
                      <button
                        type="button"
                        className="text-xs underline"
                        onClick={() => {
                          setEditingTrial(store.store_hash);
                          setTrialDates((prev) => ({
                            ...prev,
                            [store.store_hash]: store.trial_ends_at
                              ? new Date(store.trial_ends_at).toISOString().split('T')[0]
                              : '',
                          }));
                        }}
                      >
                        Edit
                      </button>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      <input
                        type="date"
                        className="border"
                        value={trialDates[store.store_hash] ?? ''}
                        onChange={(e) =>
                          setTrialDates((prev) => ({ ...prev, [store.store_hash]: e.target.value }))
                        }
                      />
                      <button type="button" className="border px-1" onClick={() => handleTrialChange(store)}>
                        Change
                      </button>
                      <button type="button" onClick={() => setEditingTrial(null)}>
                        Cancel
                      </button>
                    </div>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
