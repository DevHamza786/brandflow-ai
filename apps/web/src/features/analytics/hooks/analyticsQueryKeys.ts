import type { AnalyticsDashboardQueryParams } from '@/features/analytics/types/dashboard';

export const analyticsKeys = {
  all: ['analytics'] as const,
  dashboard: (params: AnalyticsDashboardQueryParams) =>
    [...analyticsKeys.all, 'dashboard', params] as const,
};
