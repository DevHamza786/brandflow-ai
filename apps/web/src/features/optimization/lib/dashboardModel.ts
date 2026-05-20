import { hourLabel } from '@/features/analytics/lib/format';
import type {
  AdaptiveLearningStatus,
  CtaEffectivenessPoint,
  CycleHistoryPoint,
  EngagementImprovementPoint,
  ExperimentCompareRow,
  HookTrendPoint,
  OptimizationDashboardView,
  OptimizationFilterState,
  OptimizationLoopDto,
  OptimizationOpportunity,
  OptimizationOverviewMetrics,
  OptimizationRecommendationDto,
  OptimizationSnapshotDto,
  PostingTimeOptPoint,
} from '@/features/optimization/types/dashboard';

const ENGINE_LABELS: Record<string, string> = {
  hook_structure: 'Hook structure',
  posting_time: 'Posting time',
  cta: 'CTA',
  audience_fit: 'Audience fit',
};

export function buildOptimizationDashboard(
  loops: OptimizationLoopDto[],
  snapshots: OptimizationSnapshotDto[],
  recommendations: OptimizationRecommendationDto[],
  filters: OptimizationFilterState,
  engagementDeltaPct: number | null = null,
): OptimizationDashboardView {
  const loop = loops.find((l) => l.loop_type === 'composite') ?? loops[0] ?? null;
  const filtered = filterSnapshots(snapshots, filters);
  const latestByEngine = latestSnapshotPerEngine(filtered);

  return {
    loop,
    overview: buildOverview(loop, filtered, recommendations, latestByEngine, engagementDeltaPct),
    hook_trends: buildHookTrends(filtered),
    posting_time_profile: buildPostingTimeProfile(latestByEngine.posting_time),
    cta_effectiveness: buildCtaEffectiveness(latestByEngine.cta),
    engagement_improvements: buildEngagementImprovements(latestByEngine),
    cycle_history: buildCycleHistory(filtered),
    history_timeline: [...filtered].sort(
      (a, b) => new Date(b.captured_at).getTime() - new Date(a.captured_at).getTime(),
    ),
    experiments: buildExperimentCompare(filtered, loop?.current_cycle ?? 0),
    opportunities: buildOpportunities(filtered),
    recommendations,
    adaptive: buildAdaptiveStatus(filtered, loop),
  };
}

function filterSnapshots(
  snapshots: OptimizationSnapshotDto[],
  filters: OptimizationFilterState,
): OptimizationSnapshotDto[] {
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
    return true;
  });
}

function latestSnapshotPerEngine(
  snapshots: OptimizationSnapshotDto[],
): Partial<Record<string, OptimizationSnapshotDto>> {
  const map: Partial<Record<string, OptimizationSnapshotDto>> = {};
  const sorted = [...snapshots].sort((a, b) => b.cycle_number - a.cycle_number);
  for (const s of sorted) {
    if (!map[s.engine]) {
      map[s.engine] = s;
    }
  }
  return map;
}

function buildOverview(
  loop: OptimizationLoopDto | null,
  snapshots: OptimizationSnapshotDto[],
  recommendations: OptimizationRecommendationDto[],
  latest: Partial<Record<string, OptimizationSnapshotDto>>,
  engagementDeltaPct: number | null,
): OptimizationOverviewMetrics {
  const uplifts = snapshots
    .map((s) => upliftFromSnapshot(s))
    .filter((u): u is number => u != null);
  const avgUplift =
    uplifts.length > 0 ? uplifts.reduce((a, b) => a + b, 0) / uplifts.length : null;

  return {
    current_cycle: loop?.current_cycle ?? 0,
    total_snapshots: snapshots.length,
    avg_uplift_pct: avgUplift != null ? Math.round(avgUplift * 10) / 10 : null,
    recommendations_active: recommendations.length,
    engines_with_signals: Object.keys(latest).length,
    last_run_at: loop?.last_run_at ?? null,
    engagement_delta_pct: engagementDeltaPct,
    hook_gain_pct: latest.hook_structure ? upliftFromSnapshot(latest.hook_structure) : null,
    posting_time_gain_pct: latest.posting_time ? upliftFromSnapshot(latest.posting_time) : null,
    cta_gain_pct: latest.cta ? upliftFromSnapshot(latest.cta) : null,
  };
}

function upliftFromSnapshot(s: OptimizationSnapshotDto): number | null {
  const v = s.delta_metrics.uplift_pct;
  return typeof v === 'number' ? v : null;
}

function buildHookTrends(snapshots: OptimizationSnapshotDto[]): HookTrendPoint[] {
  return snapshots
    .filter((s) => s.engine === 'hook_structure')
    .map((s) => ({
      cycle: s.cycle_number,
      uplift_pct: upliftFromSnapshot(s) ?? 0,
      style_label: str(s.delta_metrics.label) || str(s.observed_metrics.style) || 'Hook',
      score: s.score,
      captured_at: s.captured_at,
    }))
    .sort((a, b) => a.cycle - b.cycle);
}

function buildPostingTimeProfile(s?: OptimizationSnapshotDto): PostingTimeOptPoint[] {
  if (!s) {
    return [];
  }
  const histogram = asArray(s.evidence.histogram);
  if (histogram.length > 0) {
    return histogram.map((row) => {
      const r = row as Record<string, unknown>;
      const hour = num(r.hour);
      return {
        hour,
        label: hourLabel(hour),
        avg_normalized: num(r.avg_normalized),
        sample_count: num(r.sample_count),
      };
    });
  }
  const hour = num(s.observed_metrics.best_hour);
  return [
    {
      hour,
      label: hourLabel(hour),
      avg_normalized: num(s.observed_metrics.avg_normalized),
      sample_count: num(s.observed_metrics.sample_count),
    },
  ];
}

function buildCtaEffectiveness(s?: OptimizationSnapshotDto): CtaEffectivenessPoint[] {
  if (!s) {
    return [];
  }
  const cta = str(s.observed_metrics.preferred_cta) || 'Preferred CTA';
  return [
    {
      label: cta,
      with_cta_avg: num(s.observed_metrics.avg_with_cta),
      without_cta_avg: num(s.baseline_metrics.avg_without_cta),
      uplift_pct: upliftFromSnapshot(s) ?? 0,
    },
  ];
}

function buildEngagementImprovements(
  latest: Partial<Record<string, OptimizationSnapshotDto>>,
): EngagementImprovementPoint[] {
  const out: EngagementImprovementPoint[] = [];
  for (const engine of ['hook_structure', 'posting_time', 'cta', 'audience_fit']) {
    const snap = latest[engine];
    if (!snap) {
      continue;
    }
    out.push({
      engine,
      label: ENGINE_LABELS[engine] ?? engine,
      uplift_pct: upliftFromSnapshot(snap) ?? 0,
      score: snap.score,
    });
  }
  return out;
}

function buildCycleHistory(snapshots: OptimizationSnapshotDto[]): CycleHistoryPoint[] {
  return snapshots
    .map((s) => ({
      cycle: s.cycle_number,
      captured_at: s.captured_at,
      score: s.score,
      engine: s.engine,
      uplift_pct: upliftFromSnapshot(s),
    }))
    .sort((a, b) => a.cycle - b.cycle || a.engine.localeCompare(b.engine));
}

function buildExperimentCompare(
  snapshots: OptimizationSnapshotDto[],
  currentCycle: number,
): ExperimentCompareRow[] {
  const cycleB = currentCycle;
  const cycleA = Math.max(1, currentCycle - 1);
  const engines = ['hook_structure', 'posting_time', 'cta', 'audience_fit'];

  return engines.map((engine) => {
    const a = snapshots.find((s) => s.engine === engine && s.cycle_number === cycleA);
    const b = snapshots.find((s) => s.engine === engine && s.cycle_number === cycleB);
    const upliftA = a ? upliftFromSnapshot(a) : null;
    const upliftB = b ? upliftFromSnapshot(b) : null;

    return {
      engine,
      label: ENGINE_LABELS[engine] ?? engine,
      cycle_a: cycleA,
      cycle_b: cycleB,
      uplift_a: upliftA,
      uplift_b: upliftB,
      score_delta: a && b ? b.score - a.score : null,
    };
  });
}

function buildOpportunities(snapshots: OptimizationSnapshotDto[]): OptimizationOpportunity[] {
  return snapshots
    .filter((s) => s.status === 'proposed' && s.score >= 35 && s.score < 70)
    .sort((a, b) => b.score - a.score)
    .slice(0, 6)
    .map((s) => ({
      id: s.id,
      title: s.title,
      engine: s.engine,
      score: s.score,
      summary: s.summary,
      cycle_number: s.cycle_number,
    }));
}

function buildAdaptiveStatus(
  snapshots: OptimizationSnapshotDto[],
  loop: OptimizationLoopDto | null,
): AdaptiveLearningStatus {
  const arms = new Set<string>();
  let schemaVersion = 1;
  let embeddingReady = false;

  for (const s of snapshots) {
    const ml = s.ml_features;
    if (typeof ml.schema_version === 'number') {
      schemaVersion = ml.schema_version;
    }
    if (ml.bandit_arm) {
      arms.add(String(ml.bandit_arm));
    }
    if (ml.embedding_ref) {
      embeddingReady = true;
    }
  }

  const mlState = loop?.ml_state ?? {};

  return {
    schema_version: schemaVersion,
    active_arms: [...arms],
    embedding_ready: embeddingReady,
    rl_ready: Boolean(mlState.rl_policy_id),
    experiment_slots: num(mlState.experiment_slots) || 0,
    last_cycle: loop?.current_cycle ?? 0,
  };
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}

function asArray(v: unknown): unknown[] {
  return Array.isArray(v) ? v : [];
}
