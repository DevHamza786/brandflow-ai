import { chartColors } from '@/features/analytics/lib/chartTheme';
import type {
  AutonomousDashboardView,
  AutonomousFilterState,
  AutonomousOverviewMetrics,
  AutonomousSnapshotDto,
  AutonomousWorkflowDto,
  ConfidenceTrendPoint,
  DecisionOutcomePoint,
  PostingDecisionPoint,
} from '@/features/autonomous/types/dashboard';

export function buildAutonomousDashboard(
  workflow: AutonomousWorkflowDto | null,
  snapshots: AutonomousSnapshotDto[],
  filters: AutonomousFilterState,
): AutonomousDashboardView {
  const filtered = filterSnapshots(snapshots, filters);
  const overview = buildOverview(workflow, filtered);

  return {
    workflow,
    overview,
    confidence_trends: buildConfidenceTrends(filtered),
    decision_outcomes: buildOutcomes(filtered),
    posting_decisions: buildPostingDecisions(filtered),
    timeline: [...filtered].sort(
      (a, b) => new Date(b.captured_at).getTime() - new Date(a.captured_at).getTime(),
    ),
    experiments: buildExperiments(filtered, workflow?.current_cycle ?? 0),
    adaptive: {
      schema_version: 1,
      agent_ready: true,
      rl_ready: Boolean(workflow?.ml_state?.rl_policy_id),
      experiment_slots: num(workflow?.ml_state?.experiment_slots),
    },
  };
}

function filterSnapshots(
  snapshots: AutonomousSnapshotDto[],
  filters: AutonomousFilterState,
): AutonomousSnapshotDto[] {
  return snapshots.filter((s) => {
    if (filters.engine !== 'all' && s.engine !== filters.engine) {
      return false;
    }
    if (filters.cycleFrom != null && s.cycle_number < filters.cycleFrom) {
      return false;
    }
    if (filters.cycleTo != null && s.cycle_number > filters.cycleTo) {
      return false;
    }
    if (filters.statusFilter === 'blocked' && !s.status.startsWith('blocked_')) {
      return false;
    }
    if (filters.statusFilter === 'approved' && s.status !== 'approved') {
      return false;
    }
    if (filters.statusFilter === 'proposed' && s.status !== 'proposed') {
      return false;
    }
    return true;
  });
}

function buildOverview(
  workflow: AutonomousWorkflowDto | null,
  snapshots: AutonomousSnapshotDto[],
): AutonomousOverviewMetrics {
  const confidences = snapshots
    .map((s) => s.confidence)
    .filter((c): c is number => c != null);
  const blocked = snapshots.filter((s) => s.status.startsWith('blocked_')).length;
  const approved = snapshots.filter((s) => s.status === 'approved').length;
  const proposed = snapshots.filter((s) => s.status === 'proposed').length;
  const composite = snapshots.filter((s) => s.engine === 'posting_decision');
  const publishReady = composite.filter(
    (s) => s.decision_payload.should_post === true && s.status === 'approved',
  ).length;

  return {
    current_cycle: workflow?.current_cycle ?? 0,
    total_snapshots: snapshots.length,
    avg_confidence:
      confidences.length > 0
        ? Math.round((confidences.reduce((a, b) => a + b, 0) / confidences.length) * 1000) / 1000
        : null,
    blocked_count: blocked,
    approved_count: approved,
    proposed_count: proposed,
    publishing_rate_pct:
      composite.length > 0 ? Math.round((publishReady / composite.length) * 100) : null,
    min_confidence: num(workflow?.config?.min_confidence) || 0.65,
    mode: workflow?.mode ?? 'suggest',
    manual_override_enabled: workflow?.manual_override_enabled ?? true,
  };
}

function buildConfidenceTrends(snapshots: AutonomousSnapshotDto[]): ConfidenceTrendPoint[] {
  return snapshots
    .filter((s) => s.confidence != null)
    .map((s) => ({
      cycle: s.cycle_number,
      confidence: Math.round((s.confidence ?? 0) * 100),
      status: s.status,
      engine: s.engine.replace(/_/g, ' '),
    }))
    .sort((a, b) => a.cycle - b.cycle);
}

function buildOutcomes(snapshots: AutonomousSnapshotDto[]): DecisionOutcomePoint[] {
  const buckets: Record<string, number> = {};
  for (const s of snapshots) {
    const key = s.status.startsWith('blocked_') ? 'blocked' : s.status;
    buckets[key] = (buckets[key] ?? 0) + 1;
  }
  const colors: Record<string, string> = {
    approved: chartColors.emerald,
    proposed: chartColors.accent,
    blocked: chartColors.rose,
    skipped: chartColors.slate,
  };

  return Object.entries(buckets).map(([label, count]) => ({
    label,
    count,
    fill: colors[label] ?? chartColors.violet,
  }));
}

function buildPostingDecisions(snapshots: AutonomousSnapshotDto[]): PostingDecisionPoint[] {
  return snapshots
    .filter((s) => s.engine === 'posting_decision')
    .map((s) => ({
      cycle: `C${s.cycle_number}`,
      should_post: s.decision_payload.should_post === true ? 1 : 0,
      hold: s.decision_payload.should_post === true ? 0 : 1,
      confidence: Math.round((s.confidence ?? 0) * 100),
    }));
}

function buildExperiments(
  snapshots: AutonomousSnapshotDto[],
  currentCycle: number,
): AutonomousDashboardView['experiments'] {
  const cycleB = currentCycle;
  const cycleA = Math.max(1, currentCycle - 1);
  const engines = ['posting_time_decision', 'content_selection', 'posting_decision'];

  return engines.map((engine) => {
    const a = snapshots.find((s) => s.engine === engine && s.cycle_number === cycleA);
    const b = snapshots.find((s) => s.engine === engine && s.cycle_number === cycleB);
    return {
      label: engine.replace(/_/g, ' '),
      cycle_a: cycleA,
      cycle_b: cycleB,
      conf_a: a?.confidence ?? null,
      conf_b: b?.confidence ?? null,
    };
  });
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}
