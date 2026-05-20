import type { HookDimensions, HookVariant, WorkflowPollingStatus } from '@/shared/types/api';

export type { HookVariant, HookDimensions, WorkflowPollingStatus };

/** Ranked variant with stable id for list keys and A/B analytics hooks. */
export type RankedHookVariant = HookVariant & {
  id: string;
  rank: number;
  isBest: boolean;
};

export type ScoreDimensionRow = {
  key: string;
  label: string;
  value: number;
};

export type HookResultsMetadata = {
  provider: string | null;
  model: string | null;
  promptVersion: string | null;
  experimentId: string | null;
  experimentVariant: string | null;
  workflowSlug: string | null;
  agentSlug: string | null;
  traceId: string | null;
  outputId: string | null;
  /** Reserved for future A/B and replay surfaces */
  analyticsReady: boolean;
};

export type HookResultsTimestamps = {
  createdAt: string | null;
  startedAt: string | null;
  completedAt: string | null;
  updatedAt: string | null;
};

/** Analytics-ready view model — decoupled from raw API envelope. */
export type HookResultsViewModel = {
  agentRunId: string;
  status: WorkflowPollingStatus;
  isPolling: boolean;
  overallScore: number | null;
  variants: RankedHookVariant[];
  dimensions: ScoreDimensionRow[];
  suggestions: string[];
  metadata: HookResultsMetadata;
  timestamps: HookResultsTimestamps;
  errorMessage: string | null;
  hasDisplayableResults: boolean;
};
