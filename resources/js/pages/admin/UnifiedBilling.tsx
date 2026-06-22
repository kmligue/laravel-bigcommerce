import { useState } from 'react';
import toast from 'react-hot-toast';
import api from '../../api/client';
import PageTitle from '../../components/PageTitle';

export default function UnifiedBilling() {
  const appName = window.__APP__.appName;
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<unknown>(null);

  const handleCreate = async () => {
    setLoading(true);

    try {
      const res = await api.post('/api/limonadmin/unified-billing/create');
      setResult(res.data);
      toast.success('Unified billing checkout created.');
    } catch {
      toast.error('Failed to create unified billing checkout.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <PageTitle title={`Limon Admin / ${appName} / Unified Billing`} />

      <div className="bg-white shadow-md p-5 mt-8">
        <button
          type="button"
          onClick={handleCreate}
          disabled={loading}
          className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-60"
        >
          {loading ? 'Creating...' : 'Create'}
        </button>

        {result !== null && (
          <pre className="mt-4 p-4 bg-gray-100 rounded text-xs overflow-auto">{JSON.stringify(result, null, 2)}</pre>
        )}
      </div>
    </>
  );
}
