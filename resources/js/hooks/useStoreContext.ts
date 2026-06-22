import { useEffect, useState } from 'react';
import api, { storeApiPath } from '../api/client';
import type { StoreContext } from '../types';

export function useStoreContext(storeHashShort: string) {
  const [context, setContext] = useState<StoreContext | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    api
      .get<StoreContext>(storeApiPath(storeHashShort, 'context'))
      .then((res) => {
        if (!cancelled) {
          setContext(res.data);
          setError(null);
        }
      })
      .catch(() => {
        if (!cancelled) {
          setError('Failed to load store context.');
        }
      })
      .finally(() => {
        if (!cancelled) {
          setLoading(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [storeHashShort]);

  return { context, loading, error };
}

export function formatDate(isoDate: string): string {
  try {
    return new Intl.DateTimeFormat(navigator.language, {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(isoDate));
  } catch {
    return isoDate;
  }
}

export function getClientDaysLeft(trialEndIso: string): number {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const endDate = new Date(trialEndIso);
  endDate.setHours(23, 59, 59, 999);
  const timeDiff = endDate.getTime() - today.getTime();
  return Math.max(0, Math.floor(timeDiff / (1000 * 60 * 60 * 24)));
}
