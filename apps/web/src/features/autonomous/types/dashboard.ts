/** Autonomous intelligence read models */

export type AutonomousMode = 'observe' | 'suggest' | 'execute';
export type AutonomousEngineFilter = 'all' | 'posting_time_decision' | 'content_selection' | 'posting_decision';

export type AutonomousFilterState = {
  engine: AutonomousEngineFilter;
  cycleFrom: number | null;
  cycleTo: number | null;
  statusFilter: 'all' | 'blocked' | 'approved' | 'proposed';
};

export type AutonomousWorkflowDto = {
  id: string;
  workspace_id: string;
  status: string;
  mode: AutonomousMode;
  correlation_key: string;
  current_cycle: number;
  config: Record<string, unknown>;
  ml_state: Record<string, unknown>;
  manual_override_enabled: boolean;
  autonomous_execution_enabled: boolean;
  last_run_at: string | null;
};

export type AutonomousSnapshotDto = {
  id: string;
  workspace_id: string;
  autonomous_workflow_id: string;
  cycle_number: number;
  status: string;
  decision_type: string;
  engine: string;
  focus: string;
  score: number;
  confidence: number | null;
  title: string;
  summary: string;
  blocked_reason: string | null;
  decision_payload: Record<string, unknown>;
  evidence: Record<string, unknown>;
  action_payload: Record<string, unknown>;
  captured_at: string;
};

export type AutonomousOverviewMetrics = {
  current_cycle: number;
  total_snapshots: number;
  avg_confidence: number | null;
  blocked_count: number;
  approved_count: number;
  proposed_count: number;
  publishing_rate_pct: number | null;
  min_confidence: number;
  mode: AutonomousMode;
  manual_override_enabled: boolean;
};

export type ConfidenceTrendPoint = {
  cycle: number;
  confidence: number;
  status: string;
  engine: string;
};

export type DecisionOutcomePoint = {
  label: string;
  count: number;
  fill: string;
};

export type PostingDecisionPoint = {
  cycle: string;
  should_post: number;
  hold: number;
  confidence: number;
};

export type AutonomousDashboardView = {
  workflow: AutonomousWorkflowDto | null;
  overview: AutonomousOverviewMetrics;
  confidence_trends: ConfidenceTrendPoint[];
  decision_outcomes: DecisionOutcomePoint[];
  posting_decisions: PostingDecisionPoint[];
  timeline: AutonomousSnapshotDto[];
  experiments: { label: string; cycle_a: number; cycle_b: number; conf_a: number | null; conf_b: number | null }[];
  adaptive: {
    schema_version: number;
    agent_ready: boolean;
    rl_ready: boolean;
    experiment_slots: number;
  };
};

export type RunAutonomousExecutionResponse = {
  workflow: AutonomousWorkflowDto;
  cycle_number: number;
  snapshots_created: number;
  blocked_count: number;
  approved_count: number;
  counts_by_status: Record<string, number>;
  snapshots: AutonomousSnapshotDto[];
};
