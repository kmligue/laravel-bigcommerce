import { useState } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { storeApiPath } from '../../api/client';

export default function Welcome() {
  const { storeHash } = useParams<{ storeHash: string }>();
  const appName = window.__APP__.appName;
  const [loading, setLoading] = useState(false);

  const handleGetStarted = async () => {
    if (!storeHash) return;
    setLoading(true);

    try {
      const res = await api.post(storeApiPath(storeHash, 'welcome'));
      if (res.data.redirect) {
        window.location.href = res.data.redirect;
      }
    } catch {
      toast.error('Something went wrong. Please try again.');
      setLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100">
      <div className="text-center w-full max-w-[1000px] mx-auto px-4">
        <h1 className="text-4xl font-bold text-gray-800 mb-4">Welcome to {appName}.</h1>
        <p className="text-gray-600 mb-6">
          Your BigCommerce store just got an upgrade. Our smart, easy-to-use app is designed to streamline your
          operations, helping you save time, sell more, and grow your business—all from within BigCommerce.
        </p>
        <p className="text-gray-600 mb-6">
          If there are any questions, email us at{' '}
          <a href="mailto:support@limonlabs.dev" className="text-blue-500 hover:text-blue-600">
            support@limonlabs.dev
          </a>
        </p>
        <button
          type="button"
          onClick={handleGetStarted}
          disabled={loading}
          className="px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition disabled:opacity-60"
        >
          {loading ? 'Loading...' : 'Get Started'}
        </button>
      </div>
    </div>
  );
}
