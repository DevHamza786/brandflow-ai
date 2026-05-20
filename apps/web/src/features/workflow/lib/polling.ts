import type { WorkflowPollingStatus } from '@/shared/types/api';
import { env } from '@/shared/config/env';

/** Default 2s — overridable via VITE_POLL_INTERVAL_MS */
export const WORKFLOW_POLL_INTERVAL_MS = env.pollIntervalMs;

export const TERMINAL_WORKFLOW_STATUSES: readonly WorkflowPollingStatus[] = [
  'completed',
  'failed',
] as const;

export const IN_FLIGHT_WORKFLOW_STATUSES: readonly WorkflowPollingStatus[] = [
  'queued',
  'running',
] as const;

export function isTerminalWorkflowStatus(
  status: WorkflowPollingStatus | undefined,
): boolean {
  return status === 'completed' || status === 'failed';
}

export function isInFlightWorkflowStatus(
  status: WorkflowPollingStatus | undefined,
): boolean {
  return status === 'queued' || status === 'running';
}

/** React Query `refetchInterval` — `false` stops polling (no memory leak / API spam). */
export function resolveWorkflowPollInterval(
  status: WorkflowPollingStatus | undefined,
): number | false {
  return isInFlightWorkflowStatus(status) ? WORKFLOW_POLL_INTERVAL_MS : false;
}
