import { useSearchParams } from 'react-router-dom';
import PageTitle from '../../components/PageTitle';

export default function Overview() {
  const [searchParams] = useSearchParams();
  const showUpgrade = searchParams.get('action') === 'upgrade';
  const success = searchParams.get('success') === 'true';

  return (
    <>
      <PageTitle title="Overview" />
      <div className="bg-white shadow-md p-5 mt-8">
        {showUpgrade && (
          <>
            {success ? (
              <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong className="font-bold">Success!</strong>
                <span className="block sm:inline"> Your plan has been upgraded.</span>
              </div>
            ) : (
              <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong className="font-bold">Error!</strong>
                <span className="block sm:inline"> There was an error upgrading your plan.</span>
              </div>
            )}
          </>
        )}
      </div>
    </>
  );
}
