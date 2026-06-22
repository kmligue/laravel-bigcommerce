import { Routes, Route, Navigate } from 'react-router-dom';
import StoreLayout from '../layouts/StoreLayout';
import AdminLayout from '../layouts/AdminLayout';
import Welcome from '../pages/store/Welcome';
import Overview from '../pages/store/Overview';
import Help from '../pages/store/Help';
import BillingIndex from '../pages/store/BillingIndex';
import BillingHistory from '../pages/store/BillingHistory';
import BillingPayment from '../pages/store/BillingPayment';
import AdminLogin from '../pages/admin/Login';
import AdminInstalls from '../pages/admin/Installs';
import UnifiedBilling from '../pages/admin/UnifiedBilling';

export default function AppRoutes() {
  return (
    <Routes>
      <Route path="/stores/:storeHash/welcome" element={<Welcome />} />

      <Route path="/stores/:storeHash" element={<StoreLayout />}>
        <Route path="overview" element={<Overview />} />
        <Route path="help" element={<Help />} />
        <Route path="billing" element={<BillingIndex />} />
        <Route path="billing/history" element={<BillingHistory />} />
        <Route path="billing/:plan" element={<BillingPayment />} />
      </Route>

      <Route path="/limonadmin" element={<AdminLogin />} />

      <Route element={<AdminLayout />}>
        <Route path="/limonadmin/installs" element={<AdminInstalls />} />
        <Route path="/limonadmin/unified-billing" element={<UnifiedBilling />} />
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
