import type { AnalyticsDashboardKpis } from '@/features/analytics/types/dashboard';
import { formatCompact, formatEngagementRate } from '@/features/analytics/lib/format';
import { Card, CardBody } from '@/shared/components/ui/Card';

type Props = { kpis: AnalyticsDashboardKpis };

const items = (kpis: AnalyticsDashboardKpis) => [
  { label: 'Reposts', value: formatCompact(kpis.reposts) },
  { label: 'Saves', value: formatCompact(kpis.saves) },
  {
    label: 'Normalized avg',
    value: kpis.normalized_engagement_avg != null ? kpis.normalized_engagement_avg.toFixed(3) : '—',
  },
  { label: 'Engagement rate', value: formatEngagementRate(kpis.engagement_rate_avg) },
];

export function PerformanceSummaryCards({ kpis }: Props) {
  return (
    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      {items(kpis).map((item) => (
        <Card key={item.label} className="border-border/70 bg-surface-overlay/40">
          <CardBody className="py-4">
            <p className="text-xs uppercase tracking-wider text-slate-500">{item.label}</p>
            <p className="mt-1 font-mono text-xl font-semibold text-white">{item.value}</p>
          </CardBody>
        </Card>
      ))}
    </div>
  );
}
