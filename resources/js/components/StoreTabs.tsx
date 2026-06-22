import { NavLink } from 'react-router-dom';

interface StoreTabsProps {
  storeHashShort: string;
}

export default function StoreTabs({ storeHashShort }: StoreTabsProps) {
  const base = `/stores/${storeHashShort}`;
  const tabClass = ({ isActive }: { isActive: boolean }) =>
    `px-3 py-2 ${isActive ? 'border-b-4 border-[#4B71FC] text-black' : 'text-[#4B71FC] hover:bg-[#f0f3ff]'}`;

  return (
    <div className="w-full border-b pb-1">
      <NavLink to={`${base}/overview`} className={tabClass} end>
        Overview
      </NavLink>
      <NavLink to={`${base}/help`} className={tabClass}>
        Help
      </NavLink>
      <NavLink to={`${base}/billing`} className={tabClass}>
        Billing
      </NavLink>
    </div>
  );
}
