import { useEffect, useRef } from 'react';
import {
  isInFlightWorkflowStatus,
  isTerminalWorkflowStatus,
} from '@/features/workflow/lib/polling';
import type { WorkflowPollingStatus } from '@/shared/types/api';

/** @deprecated Prefer isInFlightWorkflowStatus from @/features/workflow/lib/polling */
export function isInFlightStatus(status: WorkflowPollingStatus | undefined): boolean {
  return isInFlightWorkflowStatus(status);
}

export { isInFlightWorkflowStatus, isTerminalWorkflowStatus };

export function usePollingEffect(
  enabled: boolean,
  intervalMs: number,
  onTick: () => void,
): void {
  const onTickRef = useRef(onTick);
  onTickRef.current = onTick;

  useEffect(() => {
    if (!enabled) return;

    const id = window.setInterval(() => {
      onTickRef.current();
    }, intervalMs);

    return () => window.clearInterval(id);
  }, [enabled, intervalMs]);
}
