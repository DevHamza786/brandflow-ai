import type { OptimizationOverviewMetrics } from '@/features/optimization/types/dashboard';
import { KpiCard } from '@/features/analytics/components/kpi/KpiCard';
import { formatDeltaPct } from '@/features/analytics/lib/format';

type Props = {
  overview: OptimizationOverviewMetrics;
  loading?: boolean;
};

export function OptimizationOverviewCards({ overview, loading }: Props) {
  return (
    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <KpiCard
        label="Optimization cycle"
        value={overview.current_cycle > 0 ? `#${overview.current_cycle}` : '—'}
        hint={overview.last_run_at ? `Last run ${new Date(overview.last_run_at).toLocaleString()}` : 'Run a cycle to start'}
        loading={loading}
        className="border-emerald-500/20"
      />
      <KpiCard
        label="Avg uplift"
        value={overview.avg_uplift_pct != null ? `${overview.avg_uplift_pct.toFixed(1)}%` : '—'}
        hint={`${overview.total_snapshots} snapshots · ${overview.engines_with_signals} engines`}
        loading={loading}
      />
      <KpiCard
        label="Hook optimization"
        value={overview.hook_gain_pct != null ? `+${overview.hook_gain_pct.toFixed(1)}%` : '—'}
        deltaPositive={overview.hook_gain_pct != null ? overview.hook_gain_pct > 0 : null}
        hint="Period-over-period hook structure"
        loading={loading}
      />
      <KpiCard
        label="Engagement trend"
        value={
          overview.engagement_delta_pct != null
            ? formatDeltaPct(overview.engagement_delta_pct)
            : '—'
        }
        hint="From analytics dashboard (30d)"
        deltaPositive={
          overview.engagement_delta_pct != null ? overview.engagement_delta_pct > 0 : null
        }
        loading={loading}
      />
      <KpiCard
        label="Posting time lift"
        value={overview.posting_time_gain_pct != null ? `+${overview.posting_time_gain_pct.toFixed(1)}%` : '—'}
        loading={loading}
      />
      <KpiCard
        label="CTA effectiveness"
        value={overview.cta_gain_pct != null ? `+${overview.cta_gain_pct.toFixed(1)}%` : '—'}
        loading={loading}
      />
      <KpiCard
        label="Active recommendations"
        value={String(overview.recommendations_active)}
        hint="Synced from optimization loop"
        loading={loading}
      />
      <KpiCard
        label="Intelligence signals"
        value={String(overview.engines_with_signals)}
        hint="Engines with latest snapshot"
        loading={loading}
        className="border-violet-500/15"
      />
    </div>
  );
}
