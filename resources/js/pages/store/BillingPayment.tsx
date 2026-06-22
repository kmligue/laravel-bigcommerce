import { FormEvent, useEffect, useState } from 'react';
import { useNavigate, useOutletContext, useParams } from 'react-router-dom';
import { Elements, CardElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import toast from 'react-hot-toast';
import api, { storeApiPath } from '../../api/client';
import type { StoreContext } from '../../types';
import BillingTabs from '../../components/BillingTabs';
import TrialNotice from '../../components/TrialNotice';
import PageTitle from '../../components/PageTitle';
import LoadingSpinner from '../../components/LoadingSpinner';

const stripePromise = loadStripe(window.__APP__.stripeKey ?? '');

function PaymentForm({ clientSecret, plan }: { clientSecret: string; plan: string }) {
  const stripe = useStripe();
  const elements = useElements();
  const { storeHash } = useParams<{ storeHash: string }>();
  const navigate = useNavigate();
  const [cardHolderName, setCardHolderName] = useState('');
  const [loading, setLoading] = useState(false);

  const handlePay = async (e: FormEvent) => {
    e.preventDefault();
    if (!stripe || !elements || !storeHash) return;

    const cardElement = elements.getElement(CardElement);
    if (!cardElement) return;

    setLoading(true);

    const { setupIntent, error } = await stripe.confirmCardSetup(clientSecret, {
      payment_method: {
        card: cardElement,
        billing_details: { name: cardHolderName },
      },
    });

    if (error) {
      toast.error(error.message ?? 'Payment failed.');
      setLoading(false);
      return;
    }

    try {
      await api.post(storeApiPath(storeHash, `billing/${plan}`), {
        paymentMethod: setupIntent?.payment_method,
      });
      navigate(`/stores/${storeHash}/billing`);
    } catch {
      toast.error('Failed to complete payment.');
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handlePay} className="w-1/2 mx-auto">
      <input
        id="card-holder-name"
        type="text"
        className="w-full border rounded-md p-2 mb-4"
        placeholder="Cardholder name"
        value={cardHolderName}
        onChange={(e) => setCardHolderName(e.target.value)}
      />
      <div className="border rounded-md p-3 mb-4">
        <CardElement />
      </div>
      <button
        type="submit"
        disabled={!stripe || loading}
        className="bg-blue-700 py-2 px-10 text-white float-right mt-5 w-full disabled:opacity-60"
      >
        {loading ? 'Processing...' : 'Pay'}
      </button>
      <div className="clear-both" />
    </form>
  );
}

export default function BillingPayment() {
  const { storeHash, plan } = useParams<{ storeHash: string; plan: string }>();
  const context = useOutletContext<StoreContext>();
  const [clientSecret, setClientSecret] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!storeHash || !plan) return;

    api
      .get<{ client_secret: string }>(storeApiPath(storeHash, `billing/${plan}/setup-intent`))
      .then((res) => setClientSecret(res.data.client_secret))
      .catch(() => toast.error('Failed to load payment form.'))
      .finally(() => setLoading(false));
  }, [storeHash, plan]);

  if (loading || !clientSecret) {
    return <LoadingSpinner />;
  }

  return (
    <>
      <BillingTabs storeHashShort={storeHash!} />
      <TrialNotice notice={context.trial_notice} forceBillingDisplay />
      <PageTitle title="Payment" />

      <div className="bg-white shadow-md p-5 mt-8">
        <Elements stripe={stripePromise} options={{ clientSecret }}>
          <PaymentForm clientSecret={clientSecret} plan={plan!} />
        </Elements>
      </div>
    </>
  );
}
