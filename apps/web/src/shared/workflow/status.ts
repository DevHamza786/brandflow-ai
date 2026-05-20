import type { WorkflowPollingStatus } from '@/shared/types/api';

export const STATUS_LABELS: Record<WorkflowPollingStatus, string> = {
  queued: 'Queued',
  running: 'Running',
  completed: 'Completed',
  failed: 'Failed',
};

export function statusBadgeClass(status: WorkflowPollingStatus): string {
  const map: Record<WorkflowPollingStatus, string> = {
    queued: 'bg-slate-500/20 text-status-queued border-slate-500/30',
    running: 'bg-accent-glow text-status-running border-accent/40',
    completed: 'bg-emerald-500/15 text-status-completed border-emerald-500/30',
    failed: 'bg-red-500/15 text-status-failed border-red-500/30',
  };
  return map[status];
}
