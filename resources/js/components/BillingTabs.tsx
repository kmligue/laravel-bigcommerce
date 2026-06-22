import { NavLink } from 'react-router-dom';

interface BillingTabsProps {
  storeHashShort: string;
}

export default function BillingTabs({ storeHashShort }: BillingTabsProps) {
  const base = `/stores/${storeHashShort}/billing`;
  const tabClass = ({ isActive }: { isActive: boolean }) =>
    `px-3 py-2 text-sm ${isActive ? 'border-b-2 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]'}`;

  return (
    <div className="w-full border-b pb-1 mt-5">
      <NavLink to={base} className={tabClass} end>
        Pricing Plans
      </NavLink>
      <NavLink to={`${base}/history`} className={tabClass}>
        Billing History
      </NavLink>
    </div>
  );
}
