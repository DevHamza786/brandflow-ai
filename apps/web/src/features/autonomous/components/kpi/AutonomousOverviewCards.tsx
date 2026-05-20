import type { AutonomousOverviewMetrics } from '@/features/autonomous/types/dashboard';
import { KpiCard } from '@/features/analytics/components/kpi/KpiCard';

type Props = { overview: AutonomousOverviewMetrics; loading?: boolean };

export function AutonomousOverviewCards({ overview, loading }: Props) {
  return (
    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <KpiCard
        label="Autonomous cycle"
        value={overview.current_cycle > 0 ? `#${overview.current_cycle}` : '—'}
        hint={`Mode: ${overview.mode}`}
        loading={loading}
        className="border-sky-500/20"
      />
      <KpiCard
        label="Avg confidence"
        value={overview.avg_confidence != null ? `${(overview.avg_confidence * 100).toFixed(0)}%` : '—'}
        hint={`Threshold ${(overview.min_confidence * 100).toFixed(0)}%`}
        loading={loading}
      />
      <KpiCard
        label="Blocked decisions"
        value={String(overview.blocked_count)}
        deltaPositive={false}
        hint="Low-confidence guardrails"
        loading={loading}
      />
      <KpiCard
        label="Approved / proposed"
        value={`${overview.approved_count} / ${overview.proposed_count}`}
        loading={loading}
      />
      <KpiCard
        label="Publishing rate"
        value={
          overview.publishing_rate_pct != null ? `${overview.publishing_rate_pct}%` : '—'
        }
        hint="Approved composite decisions (no live publish yet)"
        loading={loading}
      />
      <KpiCard
        label="Manual override"
        value={overview.manual_override_enabled ? 'On' : 'Off'}
        loading={loading}
      />
    </div>
  );
}
