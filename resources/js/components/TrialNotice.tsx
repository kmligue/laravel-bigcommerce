import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import type { TrialNotice as TrialNoticeData } from '../types';
import { formatDate, getClientDaysLeft } from '../hooks/useStoreContext';

interface TrialNoticeProps {
  notice: TrialNoticeData;
  forceBillingDisplay?: boolean;
}

export default function TrialNotice({ notice, forceBillingDisplay = false }: TrialNoticeProps) {
  const [daysLeft, setDaysLeft] = useState(notice.days_left);

  useEffect(() => {
    if (notice.is_on_trial && notice.trial_ends_at && !notice.trial_ended) {
      setDaysLeft(getClientDaysLeft(notice.trial_ends_at));
    }
  }, [notice]);

  if (!notice.show && !forceBillingDisplay) {
    return null;
  }

  if (!notice.show) {
    return null;
  }

  const billingPath = `/${notice.store_hash}/billing`;
  const urgent = daysLeft <= 3 && notice.is_on_trial && !notice.trial_ended;

  let gradient = 'from-blue-100 to-blue-50';
  let buttonClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';

  if (notice.plan_expired || notice.trial_ended) {
    gradient = 'from-red-100 to-red-50';
    buttonClass = 'bg-red-600 hover:bg-red-700 focus:ring-red-500';
  } else if (urgent) {
    gradient = 'from-amber-100 to-amber-50';
    buttonClass = 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500';
  }

  return (
    <div className={`w-full bg-gradient-to-r ${gradient} rounded-lg shadow-sm my-6`}>
      <div className="px-6 py-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            {notice.plan_expired && (
              <div>
                <h3 className="text-base font-medium text-red-800">Your plan has expired</h3>
                <p className="text-sm text-red-700">Please choose a plan to continue using the app.</p>
              </div>
            )}
            {notice.trial_ended && !notice.plan_expired && (
              <div>
                <h3 className="text-base font-medium text-red-800">Your trial has ended</h3>
                <p className="text-sm text-red-700">
                  Your account has been downgraded to the {notice.post_trial_plan} plan.
                </p>
              </div>
            )}
            {notice.is_on_trial && !notice.trial_ended && urgent && (
              <div>
                <h3 className="text-base font-medium text-amber-800">Your trial is ending soon</h3>
                <p className="text-sm text-amber-700">
                  {daysLeft} {daysLeft === 1 ? 'day' : 'days'} remaining. Free trial ends on{' '}
                  {notice.trial_ends_at ? formatDate(notice.trial_ends_at) : ''}.
                </p>
              </div>
            )}
            {notice.is_on_trial && !notice.trial_ended && !urgent && (
              <div>
                <h3 className="text-base font-medium text-blue-800">
                  You are on free trial of the {notice.highest_plan} plan with access to all its features.
                </h3>
                <p className="text-sm text-blue-700">
                  {daysLeft} {daysLeft === 1 ? 'day' : 'days'} remaining. Free trial ends on{' '}
                  {notice.trial_ends_at ? formatDate(notice.trial_ends_at) : ''}.
                </p>
              </div>
            )}
          </div>
          <div>
            <Link
              to={billingPath}
              className={`inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 ${buttonClass}`}
            >
              Sign Up
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
