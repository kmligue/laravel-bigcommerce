import axios from 'axios';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const api = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
  },
  withCredentials: true,
});

export function storeApiPath(storeHashShort: string, path: string): string {
  return `/api/stores/${storeHashShort}/${path}`;
}

export default api;
