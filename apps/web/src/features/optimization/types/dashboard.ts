/** Optimization intelligence read models — loops, snapshots, aggregated dashboard VM. */

export type OptimizationEngineId =
  | 'all'
  | 'hook_structure'
  | 'posting_time'
  | 'cta'
  | 'audience_fit';

export type OptimizationFilterState = {
  engine: OptimizationEngineId;
  cycleFrom: number | null;
  cycleTo: number | null;
  lookbackDays: 30 | 60 | 90;
  comparisonDays: 30 | 60 | 90;
};

export type OptimizationLoopDto = {
  id: string;
  workspace_id: string;
  loop_type: string;
  status: string;
  correlation_key: string;
  current_cycle: number;
  config: Record<string, unknown>;
  ml_state: Record<string, unknown>;
  metadata: Record<string, unknown>;
  started_at: string;
  last_run_at: string | null;
  completed_at: string | null;
};

export type OptimizationSnapshotDto = {
  id: string;
  workspace_id: string;
  optimization_loop_id: string;
  cycle_number: number;
  status: string;
  engine: string;
  focus: string;
  score: number;
  confidence: number | null;
  title: string;
  summary: string;
  rationale: string | null;
  baseline_metrics: Record<string, unknown>;
  observed_metrics: Record<string, unknown>;
  delta_metrics: Record<string, unknown>;
  evidence: Record<string, unknown>;
  action_payload: Record<string, unknown>;
  personalization_context: Record<string, unknown>;
  ml_features: Record<string, unknown>;
  captured_at: string;
};

export type OptimizationRecommendationDto = {
  id: string;
  type: string;
  source: string;
  title: string;
  summary: string;
  score: number;
  confidence: number | null;
  generated_at: string;
  evidence: Record<string, unknown>;
  personalization_context: Record<string, unknown>;
  action_payload: Record<string, unknown>;
  cycle_number: number | null;
  optimization_loop_id: string | null;
  optimization_snapshot_id: string | null;
};

export type OptimizationOverviewMetrics = {
  current_cycle: number;
  total_snapshots: number;
  avg_uplift_pct: number | null;
  recommendations_active: number;
  engines_with_signals: number;
  last_run_at: string | null;
  engagement_delta_pct: number | null;
  hook_gain_pct: number | null;
  posting_time_gain_pct: number | null;
  cta_gain_pct: number | null;
};

export type HookTrendPoint = {
  cycle: number;
  uplift_pct: number;
  style_label: string;
  score: number;
  captured_at: string;
};

export type PostingTimeOptPoint = {
  hour: number;
  label: string;
  avg_normalized: number;
  sample_count: number;
};

export type CtaEffectivenessPoint = {
  label: string;
  with_cta_avg: number;
  without_cta_avg: number;
  uplift_pct: number;
};

export type EngagementImprovementPoint = {
  engine: string;
  label: string;
  uplift_pct: number;
  score: number;
};

export type CycleHistoryPoint = {
  cycle: number;
  captured_at: string;
  score: number;
  engine: string;
  uplift_pct: number | null;
};

export type ExperimentCompareRow = {
  engine: string;
  label: string;
  cycle_a: number;
  cycle_b: number;
  uplift_a: number | null;
  uplift_b: number | null;
  score_delta: number | null;
};

export type OptimizationOpportunity = {
  id: string;
  title: string;
  engine: string;
  score: number;
  summary: string;
  cycle_number: number;
};

export type AdaptiveLearningStatus = {
  schema_version: number;
  active_arms: string[];
  embedding_ready: boolean;
  rl_ready: boolean;
  experiment_slots: number;
  last_cycle: number;
};

export type OptimizationDashboardView = {
  loop: OptimizationLoopDto | null;
  overview: OptimizationOverviewMetrics;
  hook_trends: HookTrendPoint[];
  posting_time_profile: PostingTimeOptPoint[];
  cta_effectiveness: CtaEffectivenessPoint[];
  engagement_improvements: EngagementImprovementPoint[];
  cycle_history: CycleHistoryPoint[];
  history_timeline: OptimizationSnapshotDto[];
  experiments: ExperimentCompareRow[];
  opportunities: OptimizationOpportunity[];
  recommendations: OptimizationRecommendationDto[];
  adaptive: AdaptiveLearningStatus;
};

export type RunOptimizationCycleResponse = {
  loop: OptimizationLoopDto;
  cycle_number: number;
  snapshots_created: number;
  recommendations_synced: number;
  counts_by_engine: Record<string, number>;
  snapshots: OptimizationSnapshotDto[];
};
