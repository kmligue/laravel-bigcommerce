import { FormEvent, useState } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { storeApiPath } from '../../api/client';
import PageTitle from '../../components/PageTitle';

export default function Help() {
  const { storeHash } = useParams<{ storeHash: string }>();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!storeHash) return;

    setLoading(true);

    try {
      await api.post(storeApiPath(storeHash, 'help'), { name, email, message });
      setSent(true);
      setName('');
      setEmail('');
      setMessage('');
      toast.success('Your message has been sent successfully.');
    } catch {
      toast.error('Failed to send message. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <PageTitle title="Help" />
      <div className="bg-white shadow-md p-5 mt-8">
        {sent && (
          <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-5" role="alert">
            <strong className="font-bold">Success!</strong>
            <span className="block sm:inline"> Your message has been sent successfully.</span>
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="mb-5">
            <label htmlFor="name" className="block text-sm">
              Your Name
            </label>
            <input
              id="name"
              type="text"
              className="w-full border rounded-md p-2 mt-1"
              name="name"
              placeholder="Your Name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
          </div>

          <div className="mb-5">
            <label htmlFor="email" className="block text-sm">
              Your Email
            </label>
            <input
              id="email"
              type="email"
              className="w-full border rounded-md p-2 mt-1"
              name="email"
              placeholder="Your Email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>

          <div className="mb-5">
            <label htmlFor="message" className="block text-sm">
              Message
            </label>
            <textarea
              id="message"
              name="message"
              className="w-full border rounded-md p-2 mt-1"
              rows={5}
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              required
            />
          </div>

          <div className="mb-5 text-right">
            <button
              type="submit"
              disabled={loading}
              className="bg-blue-500 text-white px-4 py-2 rounded-md disabled:opacity-60"
            >
              {loading ? 'Sending...' : 'Send'}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
