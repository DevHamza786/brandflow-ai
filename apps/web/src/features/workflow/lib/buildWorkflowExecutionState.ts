import type { WorkflowExecutionState } from '@/features/workflow/types/workflow.types';
import { resolveWorkflowStatus } from '@/features/workflow/lib/resolveWorkflowStatus';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import type { AgentRunDetail, AgentRunResults } from '@/shared/types/api';

export function buildWorkflowExecutionState(
  agentRunId: string,
  detail: AgentRunDetail | undefined,
  results: AgentRunResults | undefined,
): WorkflowExecutionState {
  const status = resolveWorkflowStatus(results, detail);

  return {
    agentRunId,
    status,
    isPolling: isInFlightWorkflowStatus(status),
    agentRun: detail?.agent_run ?? null,
    workflowRun: detail?.workflow_run ?? null,
    results: results ?? null,
    resultsUrl: detail?.results_url ?? null,
    workflowError: results?.error ?? detail?.agent_run?.error ?? null,
    timestamps: results?.timestamps ?? detail?.timestamps ?? {},
  };
}
