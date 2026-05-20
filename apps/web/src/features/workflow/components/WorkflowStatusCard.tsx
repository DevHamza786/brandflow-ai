import { Link } from 'react-router-dom';
import { WorkflowProgress } from '@/features/workflow/components/WorkflowProgress';
import { StatusIndicator } from '@/features/workflow/components/StatusIndicator';
import { formatWorkflowError } from '@/features/workflow/lib/formatWorkflowError';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import type { WorkflowExecutionState } from '@/features/workflow/types/workflow.types';
import { Badge } from '@/shared/components/ui/Badge';
import { Button } from '@/shared/components/ui/Button';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Spinner } from '@/shared/components/ui/Spinner';
import { STATUS_LABELS, statusBadgeClass } from '@/shared/workflow/status';
import { cn } from '@/shared/lib/cn';

type Props = {
  execution: WorkflowExecutionState;
  isFetching?: boolean;
  onRefresh: () => void;
  onRetry?: () => void;
};

function formatTimestamp(value?: string | null): string {
  if (!value) return '—';
  try {
    return new Intl.DateTimeFormat(undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value));
  } catch {
    return value;
  }
}

export function WorkflowStatusCard({
  execution,
  isFetching = false,
  onRefresh,
  onRetry,
}: Props) {
  const { status, agentRun, workflowRun, workflowError, timestamps, agentRunId } = execution;
  const inFlight = isInFlightWorkflowStatus(status);
  const isFailed = status === 'failed';
  const isCompleted = status === 'completed';
  const errorMessage = formatWorkflowError(workflowError);

  return (
    <Card className={cn('transition-shadow duration-300', inFlight && 'shadow-glow')}>
      <CardHeader className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-4">
            <StatusIndicator status={status} size="lg" />
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <h2 className="text-lg font-semibold text-white">Workflow execution</h2>
                <Badge className={statusBadgeClass(status)}>{STATUS_LABELS[status]}</Badge>
                {isFetching && inFlight && (
                  <span className="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <Spinner className="h-3 w-3" />
                    Updating…
                  </span>
                )}
              </div>
              <p className="mt-1 font-mono text-xs text-slate-500">{agentRunId}</p>
            </div>
          </div>
        </div>

        <WorkflowProgress status={status} />
      </CardHeader>

      <CardBody className="space-y-6">
        {inFlight && (
          <div
            className="flex items-center gap-3 rounded-lg border border-accent/25 bg-accent/5 px-4 py-3"
            role="status"
            aria-live="polite"
          >
            <Spinner />
            <div>
              <p className="text-sm font-medium text-accent">
                {status === 'queued' ? 'Queued for AI processing' : 'AI agents are running'}
              </p>
              <p className="text-xs text-slate-500">
                Polling every 2s — updates automatically until complete.
              </p>
            </div>
          </div>
        )}

        {isFailed && (
          <div
            className="rounded-lg border border-red-500/30 bg-red-500/5 px-4 py-4"
            role="alert"
          >
            <p className="text-sm font-medium text-red-300">Workflow failed</p>
            <p className="mt-2 whitespace-pre-wrap font-mono text-xs text-slate-400">{errorMessage}</p>
            <div className="mt-4 flex flex-wrap gap-2">
              {onRetry && (
                <Button variant="secondary" onClick={onRetry}>
                  Retry status check
                </Button>
              )}
              <Link to="/generate">
                <Button variant="ghost">Start new generation</Button>
              </Link>
            </div>
          </div>
        )}

        {isCompleted && (
          <div className="rounded-lg border border-emerald-500/25 bg-emerald-500/5 px-4 py-3 text-sm text-emerald-200">
            Workflow finished successfully. Review ranked hook variants in results.
          </div>
        )}

        <dl className="grid gap-4 text-sm sm:grid-cols-2">
          {agentRun && (
            <>
              <div>
                <dt className="text-xs uppercase tracking-wide text-slate-500">Agent</dt>
                <dd className="mt-1 text-slate-200">{agentRun.slug}</dd>
              </div>
              <div>
                <dt className="text-xs uppercase tracking-wide text-slate-500">Agent status</dt>
                <dd className="mt-1 font-mono text-slate-300">{agentRun.status}</dd>
              </div>
            </>
          )}
          {workflowRun && (
            <>
              <div>
                <dt className="text-xs uppercase tracking-wide text-slate-500">Workflow</dt>
                <dd className="mt-1 text-slate-200">
                  {workflowRun.workflow_slug ?? 'hook_generation'}
                </dd>
              </div>
              <div>
                <dt className="text-xs uppercase tracking-wide text-slate-500">Workflow status</dt>
                <dd className="mt-1 font-mono text-slate-300">{workflowRun.status}</dd>
              </div>
            </>
          )}
          <div>
            <dt className="text-xs uppercase tracking-wide text-slate-500">Started</dt>
            <dd className="mt-1 text-slate-300">{formatTimestamp(timestamps.started_at)}</dd>
          </div>
          <div>
            <dt className="text-xs uppercase tracking-wide text-slate-500">Completed</dt>
            <dd className="mt-1 text-slate-300">{formatTimestamp(timestamps.completed_at)}</dd>
          </div>
        </dl>

        <div className="flex flex-col gap-3 border-t border-border pt-5 sm:flex-row sm:flex-wrap">
          <Link to={`/results/${agentRunId}`} className="w-full sm:w-auto">
            <Button className="w-full sm:w-auto" disabled={!isCompleted && !isFailed}>
              View results
            </Button>
          </Link>
          <Button
            variant="secondary"
            className="w-full sm:w-auto"
            onClick={onRefresh}
            loading={isFetching && !inFlight}
          >
            Refresh
          </Button>
          <Link
            to="/generate"
            className="inline-flex w-full items-center justify-center text-sm text-accent hover:underline sm:w-auto sm:justify-start"
          >
            New generation
          </Link>
        </div>
      </CardBody>
    </Card>
  );
}
