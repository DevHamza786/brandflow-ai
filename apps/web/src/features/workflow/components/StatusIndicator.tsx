import { cn } from '@/shared/lib/cn';
import type { WorkflowPollingStatus } from '@/shared/types/api';

const STATUS_RING: Record<WorkflowPollingStatus, string> = {
  queued: 'bg-status-queued/20 ring-status-queued/50',
  running: 'bg-accent-glow ring-accent/60',
  completed: 'bg-emerald-500/20 ring-emerald-500/50',
  failed: 'bg-red-500/20 ring-red-500/50',
};

const STATUS_DOT: Record<WorkflowPollingStatus, string> = {
  queued: 'bg-status-queued',
  running: 'bg-status-running',
  completed: 'bg-status-completed',
  failed: 'bg-status-failed',
};

type Props = {
  status: WorkflowPollingStatus;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
};

const sizeMap = {
  sm: { ring: 'h-8 w-8', dot: 'h-2 w-2' },
  md: { ring: 'h-11 w-11', dot: 'h-2.5 w-2.5' },
  lg: { ring: 'h-14 w-14', dot: 'h-3 w-3' },
};

export function StatusIndicator({ status, size = 'md', className }: Props) {
  const dims = sizeMap[size];
  const isRunning = status === 'running';

  return (
    <div
      className={cn(
        'relative flex shrink-0 items-center justify-center rounded-full ring-2',
        STATUS_RING[status],
        dims.ring,
        className,
      )}
      aria-hidden
    >
      {isRunning && (
        <span className="absolute inset-0 animate-ping rounded-full bg-accent/30" />
      )}
      <span
        className={cn(
          'relative rounded-full',
          STATUS_DOT[status],
          dims.dot,
          isRunning && 'animate-pulse',
        )}
      />
    </div>
  );
}
