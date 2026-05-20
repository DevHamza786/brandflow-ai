import { useEffect, useRef } from 'react';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import { useToast } from '@/shared/providers/ToastProvider';
import type { WorkflowPollingStatus } from '@/shared/types/api';

/**
 * Notifies on terminal transitions only (queued/running → completed/failed).
 * Skips mount-time terminal state to avoid noisy toasts when revisiting a run.
 */
export function useWorkflowStatusToasts(
  agentRunId: string | undefined,
  status: WorkflowPollingStatus,
): void {
  const toast = useToast();
  const prevStatusRef = useRef<WorkflowPollingStatus | null>(null);
  const hasSeenInFlightRef = useRef(false);

  useEffect(() => {
    if (!agentRunId) return;

    if (isInFlightWorkflowStatus(status)) {
      hasSeenInFlightRef.current = true;
    }

    const prev = prevStatusRef.current;
    if (prev !== null && prev !== status && hasSeenInFlightRef.current) {
      if (isInFlightWorkflowStatus(prev) && status === 'completed') {
        toast.push('Workflow completed — results are ready', 'success');
      }
      if (isInFlightWorkflowStatus(prev) && status === 'failed') {
        toast.push('Workflow failed — review the error below', 'error');
      }
    }

    prevStatusRef.current = status;
  }, [agentRunId, status, toast]);
}
