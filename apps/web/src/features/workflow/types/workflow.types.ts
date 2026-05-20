import type {
  AgentRun,
  AgentRunDetail,
  AgentRunResults,
  WorkflowPollingStatus,
  WorkflowRun,
} from '@/shared/types/api';

export type { AgentRun, AgentRunDetail, AgentRunResults, WorkflowPollingStatus, WorkflowRun };

/** Unified workflow execution view for status UI (extensible for multi-agent chains). */
export type WorkflowExecutionState = {
  agentRunId: string;
  status: WorkflowPollingStatus;
  isPolling: boolean;
  agentRun: AgentRun | null;
  workflowRun: WorkflowRun | null;
  results: AgentRunResults | null;
  resultsUrl: string | null;
  workflowError: Record<string, unknown> | null;
  timestamps: AgentRunResults['timestamps'];
};

export type WorkflowProgressStep = {
  id: WorkflowPollingStatus | 'failed';
  label: string;
  description: string;
};
