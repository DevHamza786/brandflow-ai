import type { AnalyticsComparison, AnalyticsDashboardKpis } from '@/features/analytics/types/dashboard';
import { formatCompact, formatDeltaPct, formatEngagementRate } from '@/features/analytics/lib/format';
import { KpiCard } from '@/features/analytics/components/kpi/KpiCard';

type Props = {
  kpis: AnalyticsDashboardKpis;
  comparison: AnalyticsComparison;
  loading?: boolean;
};

function deltaTone(delta: number | null): boolean | null {
  if (delta == null) {
    return null;
  }
  if (delta > 0) {
    return true;
  }
  if (delta < 0) {
    return false;
  }
  return null;
}

export function KpiGrid({ kpis, comparison, loading }: Props) {
  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
      <KpiCard
        label="Impressions"
        value={formatCompact(kpis.impressions)}
        delta={formatDeltaPct(comparison.impressions_delta)}
        deltaPositive={deltaTone(comparison.impressions_delta)}
        loading={loading}
      />
      <KpiCard
        label="Engagement rate"
        value={formatEngagementRate(kpis.engagement_rate_avg)}
        delta={formatDeltaPct(comparison.engagement_rate_delta)}
        deltaPositive={deltaTone(comparison.engagement_rate_delta)}
        loading={loading}
      />
      <KpiCard
        label="Likes"
        value={formatCompact(kpis.likes)}
        loading={loading}
      />
      <KpiCard
        label="Comments"
        value={formatCompact(kpis.comments)}
        loading={loading}
      />
      <KpiCard
        label="Hook score avg"
        value={kpis.hook_score_avg != null ? kpis.hook_score_avg.toFixed(1) : '—'}
        hint="Lab engine"
        loading={loading}
      />
      <KpiCard
        label="Posts tracked"
        value={String(kpis.posts_observed)}
        delta={formatDeltaPct(comparison.posts_observed_delta)}
        deltaPositive={deltaTone(comparison.posts_observed_delta)}
        loading={loading}
      />
    </div>
  );
}
