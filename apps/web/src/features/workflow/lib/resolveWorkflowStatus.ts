import type { AgentRunDetail, AgentRunResults, WorkflowPollingStatus } from '@/shared/types/api';

/** Results endpoint is the polling contract; detail is fallback on first paint. */
export function resolveWorkflowStatus(
  results: AgentRunResults | undefined,
  detail: AgentRunDetail | undefined,
): WorkflowPollingStatus {
  return results?.status ?? detail?.status ?? 'queued';
}
