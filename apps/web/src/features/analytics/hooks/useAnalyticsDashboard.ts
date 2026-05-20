import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { fetchAnalyticsDashboard } from '@/features/analytics/api/analytics.api';
import { analyticsKeys } from '@/features/analytics/hooks/analyticsQueryKeys';
import type { AnalyticsDashboardQueryParams } from '@/features/analytics/types/dashboard';

export function useAnalyticsDashboard(params: AnalyticsDashboardQueryParams) {
  return useQuery({
    queryKey: analyticsKeys.dashboard(params),
    queryFn: () => fetchAnalyticsDashboard(params),
    staleTime: 60_000,
    gcTime: 5 * 60_000,
    placeholderData: keepPreviousData,
  });
}
