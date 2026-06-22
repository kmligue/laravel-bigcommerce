import { Outlet } from 'react-router-dom';

export default function AdminLayout() {
  return (
    <>
      <div className="p-10 mx-auto max-w-[1300px]">
        <Outlet />
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
