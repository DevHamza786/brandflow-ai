import { Link, useParams } from 'react-router-dom';
import { WorkflowStatusCard } from '@/features/workflow/components/WorkflowStatusCard';
import { WorkflowStatusSkeleton } from '@/features/workflow/components/WorkflowStatusSkeleton';
import { useWorkflowStatus } from '@/features/workflow/hooks/useWorkflowStatus';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { useToast } from '@/shared/providers/ToastProvider';
import type { ApiError } from '@/shared/types/api';

export function WorkflowStatusPage() {
  const { id } = useParams<{ id: string }>();
  const toast = useToast();
  const {
    execution,
    status,
    isPolling,
    isInitialLoading,
    isError,
    error,
    isFetching,
    refetch,
  } = useWorkflowStatus(id);

  const handleRefresh = async () => {
    try {
      await refetch();
      if (!isInFlightWorkflowStatus(status)) {
        toast.push('Status updated', 'info');
      }
    } catch {
      toast.push('Could not refresh workflow status', 'error');
    }
  };

  const handleRetry = async () => {
    try {
      await refetch();
      toast.push('Re-checking workflow status…', 'info');
    } catch {
      toast.push('Retry failed — check your connection or API', 'error');
    }
  };

  if (isInitialLoading) {
    return <WorkflowStatusSkeleton />;
  }

  if (isError || !execution) {
    return (
      <div className="mx-auto max-w-2xl space-y-4">
        <ErrorState
          error={(error as ApiError) ?? new Error('Unable to load workflow status')}
          onRetry={() => void refetch()}
        />
        <Link to="/generate" className="inline-block text-sm text-accent hover:underline">
          Back to Generate Hooks
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <header className="max-w-3xl">
        <p className="text-xs font-medium uppercase tracking-widest text-accent">Workflow</p>
        <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
          Execution status
        </h1>
        <p className="mt-3 text-sm leading-relaxed text-slate-400">
          {isPolling
            ? 'Live polling is active — status updates every 2 seconds until the workflow finishes.'
            : 'Polling has stopped — this run reached a terminal state.'}
        </p>
      </header>

      <WorkflowStatusCard
        execution={execution}
        isFetching={isFetching}
        onRefresh={() => void handleRefresh()}
        onRetry={() => void handleRetry()}
      />
    </div>
  );
}
