import { useEffect, useState } from 'react';
import { useOutletContext, useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { storeApiPath } from '../../api/client';
import type { BillingData, StoreContext } from '../../types';
import BillingTabs from '../../components/BillingTabs';
import TrialNotice from '../../components/TrialNotice';
import PageTitle from '../../components/PageTitle';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function BillingIndex() {
  const { storeHash } = useParams<{ storeHash: string }>();
  const context = useOutletContext<StoreContext>();
  const [data, setData] = useState<BillingData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!storeHash) return;

    api
      .get<BillingData>(storeApiPath(storeHash, 'billing'))
      .then((res) => setData(res.data))
      .catch(() => toast.error('Failed to load billing data.'))
      .finally(() => setLoading(false));
  }, [storeHash]);

  const handlePlanSelect = async (planKey: string) => {
    if (!storeHash) return;

    const confirmed = window.confirm('Are you sure?');
    if (!confirmed) return;

    try {
      const res = await api.post(storeApiPath(storeHash, `billing/${planKey}/select`));
      if (res.data.success) {
        if (res.data.url) {
          window.parent.location.href = res.data.url;
        } else {
          window.location.reload();
        }
      }
    } catch {
      toast.error('An error occurred. Please try again.');
    }
  };

  const handleCancelSubscription = async (endOfPeriod: boolean) => {
    if (!storeHash) return;

    try {
      const res = await api.post(storeApiPath(storeHash, 'billing/cancel'), {
        end_of_period: endOfPeriod ? 1 : 0,
      });

      if (res.data.success) {
        toast.success(res.data.message ?? 'Subscription canceled successfully');
        setTimeout(() => window.location.reload(), 2000);
      } else {
        toast.error(res.data.message ?? 'Failed to cancel subscription.');
      }
    } catch {
      toast.error('An error occurred. Please try again.');
    }
  };

  const promptCancelSubscription = () => {
    const immediate = window.confirm(
      'Cancel Subscription\n\nClick OK to cancel immediately, or Cancel to choose end-of-period option.'
    );

    if (immediate) {
      handleCancelSubscription(false);
      return;
    }

    const endOfPeriod = window.confirm('Cancel at end of billing period?');
    if (endOfPeriod) {
      handleCancelSubscription(true);
    }
  };

  if (loading || !data) {
    return <LoadingSpinner />;
  }

  const { is_on_trial: isOnTrial } = data;

  return (
    <>
      <BillingTabs storeHashShort={storeHash!} />
      <TrialNotice notice={context.trial_notice} forceBillingDisplay />
      <PageTitle title="Pricing Plans" />

      <div className="bg-white shadow-md p-5 mt-8">
        <div className="py-6 md:py-12">
          <div className="lg:flex lg:-mx-4 mt-6 md:mt-12 flex-wrap">
            {data.plans.map((plan) => (
              <div key={plan.key} className="lg:w-1/3 my-4 md:my-6 px-4">
                <div
                  className={`border border-indigo-600 border-solid text-center max-w-sm mx-auto transition-colors duration-300 relative min-h-[565px] ${
                    plan.is_current ? 'bg-indigo-700 text-white' : 'bg-slate-50'
                  }`}
                >
                  {plan.is_trial_plan && (
                    <div className="absolute -top-2.5 right-2.5 bg-emerald-500 text-white text-xs font-semibold px-3 py-1 rounded-full shadow z-10">
                      Free Trial
                    </div>
                  )}

                  <div className="p-6 md:py-8">
                    <h4 className="font-medium leading-tight text-2xl mb-2">{plan.name}</h4>
                  </div>
                  <div
                    className={`p-6 transition-colors duration-300 ${
                      plan.is_current ? 'bg-indigo-600' : 'bg-indigo-100'
                    }`}
                  >
                    <div>
                      <span className="text-4xl font-semibold">${plan.price}</span> /month
                    </div>
                  </div>
                  <div className="p-6">
                    <ul className="leading-loose">
                      {plan.features.map((feature) => (
                        <li key={feature}>{feature}</li>
                      ))}
                    </ul>
                    <div className="mt-6 py-4">
                      {!isOnTrial && plan.is_current ? (
                        <div>
                          <span className="bg-indigo-600 text-xl text-white py-2 px-6 rounded transition-colors duration-300 mb-2 block">
                            Current
                          </span>
                          {plan.show_cancel && (
                            <button
                              type="button"
                              onClick={promptCancelSubscription}
                              className="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mt-3 w-full"
                            >
                              Cancel Subscription
                            </button>
                          )}
                        </div>
                      ) : (
                        <button
                          type="button"
                          onClick={() => handlePlanSelect(plan.key)}
                          className={`${
                            plan.is_current
                              ? 'bg-indigo-600 hover:bg-indigo-700'
                              : 'bg-slate-400 hover:bg-slate-500'
                          } text-xl text-white py-2 px-6 rounded transition-colors duration-300 w-full`}
                        >
                          {plan.button_text}
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  );
}
