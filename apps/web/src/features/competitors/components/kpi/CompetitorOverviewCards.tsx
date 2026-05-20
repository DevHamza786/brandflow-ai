import type { CompetitorIntelligenceReport } from '@/features/competitors/types/dashboard';
import { formatCompact, formatEngagementRate } from '@/features/analytics/lib/format';
import { Skeleton } from '@/shared/components/ui/Skeleton';

type Props = {
  report: CompetitorIntelligenceReport | undefined;
  loading?: boolean;
};

function Card({
  label,
  value,
  hint,
  loading,
}: {
  label: string;
  value: string;
  hint?: string;
  loading?: boolean;
}) {
  if (loading) {
    return (
      <div className="rounded-xl border border-border/80 bg-surface-raised/80 p-4">
        <Skeleton className="h-3 w-24" />
        <Skeleton className="mt-3 h-8 w-16" />
      </div>
    );
  }

  return (
    <div className="rounded-xl border border-border/80 bg-surface-raised/80 p-4 transition-all hover:border-accent/25">
      <p className="text-xs font-medium uppercase tracking-wider text-slate-500">{label}</p>
      <p className="mt-2 font-mono text-2xl font-semibold text-white">{value}</p>
      {hint && <p className="mt-1 text-xs text-slate-500">{hint}</p>}
    </div>
  );
}

export function CompetitorOverviewCards({ report, loading }: Props) {
  const snap = report?.latest_snapshot;
  const dominant = snap?.hook_patterns.dominant_style ?? '—';
  const dominantLabel =
    snap?.hook_patterns.styles?.find((s) => s.style === dominant)?.label ?? dominant;

  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
      <Card
        label="Intelligence score"
        value={report?.intelligence_score != null ? report.intelligence_score.toFixed(1) : '—'}
        loading={loading}
      />
      <Card
        label="Engagement rate"
        value={formatEngagementRate(snap?.avg_engagement_rate)}
        loading={loading}
      />
      <Card
        label="Posts / week"
        value={snap?.posts_per_week != null ? snap.posts_per_week.toFixed(1) : '—'}
        loading={loading}
      />
      <Card
        label="Posts tracked"
        value={snap != null ? String(snap.posts_count) : '—'}
        loading={loading}
      />
      <Card label="Top hook style" value={dominantLabel} loading={loading} />
      <Card
        label="Total impressions"
        value={formatCompact(
          Number(snap?.engagement_metrics?.total_impressions ?? 0),
        )}
        loading={loading}
      />
    </div>
  );
}
