import { useCallback, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import type {
  AnalyticsDashboardQueryParams,
  AnalyticsDatePreset,
  DashboardFilterState,
} from '@/features/analytics/types/dashboard';

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

function presetRange(preset: AnalyticsDatePreset): { from: string; to: string } {
  const to = new Date();
  const from = new Date();
  const days = preset === '7d' ? 7 : preset === '90d' ? 90 : 30;
  from.setDate(from.getDate() - (days - 1));
  return {
    from: from.toISOString().slice(0, 10),
    to: to.toISOString().slice(0, 10),
  };
}

export function filtersToQueryParams(state: DashboardFilterState): AnalyticsDashboardQueryParams {
  if (state.preset === 'custom' && state.from && state.to) {
    return { from: state.from, to: state.to };
  }
  if (state.preset !== 'custom') {
    return { preset: state.preset };
  }
  return { preset: '30d' };
}

export function useDashboardFilters(): {
  filters: DashboardFilterState;
  queryParams: AnalyticsDashboardQueryParams;
  setPreset: (preset: AnalyticsDatePreset) => void;
  setCustomRange: (from: string, to: string) => void;
} {
  const [searchParams, setSearchParams] = useSearchParams();

  const filters = useMemo((): DashboardFilterState => {
    const presetParam = searchParams.get('preset');
    const from = searchParams.get('from');
    const to = searchParams.get('to');

    if (from && to) {
      return { preset: 'custom', from, to };
    }
    if (presetParam === '7d' || presetParam === '30d' || presetParam === '90d') {
      const range = presetRange(presetParam);
      return { preset: presetParam, from: range.from, to: range.to };
    }

    const defaultPreset: AnalyticsDatePreset = '30d';
    const range = presetRange(defaultPreset);
    return { preset: defaultPreset, from: range.from, to: range.to };
  }, [searchParams]);

  const queryParams = useMemo(() => filtersToQueryParams(filters), [filters]);

  const setPreset = useCallback(
    (preset: AnalyticsDatePreset) => {
      setSearchParams({ preset }, { replace: true });
    },
    [setSearchParams],
  );

  const setCustomRange = useCallback(
    (from: string, to: string) => {
      if (!from || !to || from > to) {
        return;
      }
      setSearchParams({ from, to }, { replace: true });
    },
    [setSearchParams],
  );

  return { filters, queryParams, setPreset, setCustomRange };
}

export function defaultCustomEnd(): string {
  return todayIso();
}
