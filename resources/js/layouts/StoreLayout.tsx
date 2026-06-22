import { Outlet, useParams } from 'react-router-dom';
import StoreTabs from '../components/StoreTabs';
import TrialNotice from '../components/TrialNotice';
import LoadingSpinner from '../components/LoadingSpinner';
import { useStoreContext } from '../hooks/useStoreContext';

export default function StoreLayout() {
  const { storeHash } = useParams<{ storeHash: string }>();
  const storeHashShort = storeHash ?? '';
  const { context, loading, error } = useStoreContext(storeHashShort);
  const isBilling = location.pathname.includes('/billing');

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error || !context) {
    return <div className="p-10 text-red-600">{error ?? 'Unable to load store.'}</div>;
  }

  return (
    <>
      <div className="p-10 mx-auto max-w-[1300px]">
        <StoreTabs storeHashShort={storeHashShort} />
        {!isBilling && <TrialNotice notice={context.trial_notice} />}
        <Outlet context={context} />
      </div>
      <div className="pl-10 pr-10 pb-10 pt-0 mx-auto max-w-[1300px]">
        <div>
          COPYRIGHT &copy; {new Date().getFullYear()}{' '}
          <a href="https://limonlabs.dev/" target="_blank" rel="noreferrer" className="text-blue-600">
            Limon Labs
          </a>
          . ALL RIGHTS RESERVED
        </div>
      </div>
    </>
  );
}
