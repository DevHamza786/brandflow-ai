import {
  WORKFLOW_FAILED_STEP,
  WORKFLOW_PROGRESS_STEPS,
} from '@/features/workflow/constants/progress-steps';
import { cn } from '@/shared/lib/cn';
import type { WorkflowPollingStatus } from '@/shared/types/api';

function stepIndex(status: WorkflowPollingStatus): number {
  if (status === 'failed') return -1;
  const map: Record<WorkflowPollingStatus, number> = {
    queued: 0,
    running: 1,
    completed: 2,
    failed: -1,
  };
  return map[status];
}

type Props = {
  status: WorkflowPollingStatus;
  className?: string;
};

export function WorkflowProgress({ status, className }: Props) {
  const activeIdx = stepIndex(status);
  const isFailed = status === 'failed';
  const steps = isFailed
    ? [...WORKFLOW_PROGRESS_STEPS.slice(0, 2), WORKFLOW_FAILED_STEP]
    : WORKFLOW_PROGRESS_STEPS;

  return (
    <ol className={cn('grid gap-3 sm:grid-cols-3', className)} aria-label="Workflow progress">
      {steps.map((step, idx) => {
        const isFailedStep = step.id === 'failed';
        const isComplete = !isFailed && activeIdx > idx;
        const isActive =
          (isFailed && isFailedStep) ||
          (!isFailed && activeIdx === idx) ||
          (isFailed && idx < 2 && activeIdx >= idx);

        return (
          <li
            key={step.id}
            className={cn(
              'relative rounded-xl border px-4 py-3 transition-all duration-300',
              isActive && !isFailedStep && 'border-accent/50 bg-accent/10 shadow-glow',
              isComplete && 'border-emerald-500/30 bg-emerald-500/5',
              isFailedStep && isFailed && 'border-red-500/40 bg-red-500/10',
              !isActive && !isComplete && !isFailedStep && 'border-border bg-surface-overlay/40',
              !isActive && !isComplete && !isFailed && 'opacity-60',
            )}
          >
            <div className="flex items-start gap-3">
              <span
                className={cn(
                  'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                  isComplete && 'bg-emerald-500/20 text-emerald-400',
                  isActive && !isFailedStep && 'bg-accent/25 text-accent',
                  isFailedStep && isFailed && 'bg-red-500/20 text-red-400',
                  !isActive && !isComplete && 'bg-surface-raised text-slate-500',
                )}
              >
                {isComplete ? '✓' : idx + 1}
              </span>
              <div>
                <p className="text-sm font-medium text-slate-200">{step.label}</p>
                <p className="mt-0.5 text-xs text-slate-500">{step.description}</p>
              </div>
            </div>
          </li>
        );
      })}
    </ol>
  );
}
