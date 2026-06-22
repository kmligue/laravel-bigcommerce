import { useEffect, useState } from 'react';
import { useOutletContext, useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { storeApiPath } from '../../api/client';
import type { Invoice, StoreContext } from '../../types';
import { formatDate } from '../../hooks/useStoreContext';
import BillingTabs from '../../components/BillingTabs';
import TrialNotice from '../../components/TrialNotice';
import PageTitle from '../../components/PageTitle';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function BillingHistory() {
  const { storeHash } = useParams<{ storeHash: string }>();
  const context = useOutletContext<StoreContext>();
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!storeHash) return;

    api
      .get<{ invoices: Invoice[] }>(storeApiPath(storeHash, 'billing/history'))
      .then((res) => setInvoices(res.data.invoices))
      .catch(() => toast.error('Failed to load billing history.'))
      .finally(() => setLoading(false));
  }, [storeHash]);

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <>
      <BillingTabs storeHashShort={storeHash!} />
      <TrialNotice notice={context.trial_notice} forceBillingDisplay />
      <PageTitle title="Billing History" />

      <div className="bg-white shadow-md p-5 mt-8">
        <table className="border-collapse table-auto w-full text-sm">
          <thead>
            <tr>
              <th className="border-b border-[#9a9da1] font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left">
                Date
              </th>
              <th className="border-b border-[#9a9da1] font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left" />
            </tr>
          </thead>
          <tbody className="bg-white">
            {invoices.map((invoice, index) => (
              <tr key={`${invoice.date}-${index}`}>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{formatDate(invoice.date)}</td>
                <td className="border-b border-[#d1d5db] p-4 pl-8 text-slate-500">{invoice.description}</td>
              </tr>
            ))}
            {invoices.length === 0 && (
              <tr>
                <td colSpan={2} className="p-4 pl-8 text-slate-500">
                  No invoices found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </>
  );
}
