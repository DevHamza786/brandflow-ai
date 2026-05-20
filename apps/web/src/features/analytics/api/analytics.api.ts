import { apiGet } from '@/shared/api/client';
import type {
  AnalyticsDashboardDto,
  AnalyticsDashboardQueryParams,
} from '@/features/analytics/types/dashboard';

function buildDashboardQuery(params: AnalyticsDashboardQueryParams): string {
  const q = new URLSearchParams();
  if (params.preset) {
    q.set('preset', params.preset);
  }
  if (params.from) {
    q.set('from', params.from);
  }
  if (params.to) {
    q.set('to', params.to);
  }
  const s = q.toString();
  return s ? `?${s}` : '';
}

export async function fetchAnalyticsDashboard(
  params: AnalyticsDashboardQueryParams,
): Promise<AnalyticsDashboardDto> {
  return apiGet<AnalyticsDashboardDto>(
    `/analytics/dashboard${buildDashboardQuery(params)}`,
  );
}
