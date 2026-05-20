/** Unified polling status returned by GET /agents/runs/{id}/results */
export type WorkflowPollingStatus = 'queued' | 'running' | 'completed' | 'failed';

export type ApiSuccessResponse<T> = {
  success: true;
  data: T;
};

export type ApiProblemResponse = {
  success: false;
  type?: string;
  title?: string;
  status?: number;
  detail?: string;
  context?: Record<string, unknown>;
};

export type ApiError = {
  message: string;
  status?: number;
  detail?: string;
  context?: Record<string, unknown>;
};

export type AgentRunStatus = 'queued' | 'running' | 'completed' | 'failed' | 'cancelled';

export type AgentRun = {
  id: string;
  slug: string;
  status: AgentRunStatus;
  input: Record<string, unknown>;
  options: Record<string, unknown>;
  output?: Record<string, unknown> | null;
  error?: Record<string, unknown> | null;
  trace_id?: string | null;
  workflow_run_id?: string | null;
  started_at?: string | null;
  completed_at?: string | null;
  created_at?: string | null;
};

export type WorkflowRun = {
  id: string;
  status: string;
  workflow_slug?: string;
  current_step_id?: string | null;
  context?: Record<string, unknown>;
  error?: Record<string, unknown> | null;
  started_at?: string | null;
  completed_at?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type HookDimensions = Record<string, number>;

export type HookVariant = {
  text: string;
  overall: number;
  dimensions: HookDimensions;
  experiment_variant?: string | null;
};

export type GeneratedOutput = {
  id: string;
  type: string;
  status: string;
  provider?: string | null;
  model?: string | null;
  prompt_version?: string | null;
  primary?: {
    overall?: number;
    hook_text?: string;
    dimensions?: HookDimensions;
    suggestions?: string[];
  } | null;
  variants: HookVariant[];
  dimensions: HookDimensions;
  suggestions: string[];
  scores: Record<string, unknown>;
  metadata: Record<string, unknown>;
  created_at?: string | null;
  updated_at?: string | null;
};

/** Stable polling contract from ResultsController */
export type AgentRunResults = {
  status: WorkflowPollingStatus;
  outputs: GeneratedOutput[];
  scores: Record<string, unknown>;
  metadata: Record<string, unknown>;
  variants: HookVariant[];
  dimensions: HookDimensions;
  suggestions: string[];
  error: Record<string, unknown> | null;
  timestamps: {
    created_at?: string | null;
    started_at?: string | null;
    completed_at?: string | null;
    updated_at?: string | null;
  };
};

export type AgentRunDetail = {
  status: WorkflowPollingStatus;
  agent_run: AgentRun | null;
  workflow_run: WorkflowRun | null;
  results_url: string | null;
  timestamps: AgentRunResults['timestamps'];
};

export type HookGenerationAccept = {
  agent_run: AgentRun;
  workflow_run: WorkflowRun & { workflow_slug?: string };
  hook_score?: unknown | null;
  was_replayed: boolean;
  poll_url?: string;
  detail_url?: string;
};

export type GenerateHooksPayload = {
  options?: {
    max_variants?: number;
    target_audience?: string;
    content_pillar?: string;
  };
};
