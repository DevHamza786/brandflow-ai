import { Link, useParams } from 'react-router-dom';
import { ResultsViewer } from '@/features/results/components/ResultsViewer';
import { ResultsViewerSkeleton } from '@/features/results/components/ResultsViewerSkeleton';
import { useHookResults } from '@/features/results/hooks/useHookResults';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { Button } from '@/shared/components/ui/Button';
import { Spinner } from '@/shared/components/ui/Spinner';
import type { ApiError } from '@/shared/types/api';

export function ResultsViewerPage() {
  const { id } = useParams<{ id: string }>();
  const { viewModel, isInitialLoading, isPolling, isFetching, isError, error, refetch } =
    useHookResults(id);

  if (isInitialLoading) {
    return <ResultsViewerSkeleton />;
  }

  if (isError) {
    return (
      <div className="mx-auto max-w-2xl space-y-4">
        <ErrorState
          error={(error as ApiError) ?? new Error('Failed to load results')}
          onRetry={() => void refetch()}
        />
        <Link to={`/runs/${id}`} className="inline-block text-sm text-accent hover:underline">
          View workflow status
        </Link>
      </div>
    );
  }

  if (!viewModel) {
    return (
      <EmptyState title="Run not found" description="This agent run could not be loaded." />
    );
  }

  const isFailed = viewModel.status === 'failed';
  const showViewer =
    viewModel.hasDisplayableResults ||
    viewModel.status === 'completed' ||
    viewModel.status === 'failed';

  return (
    <div className="space-y-8">
      <header className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="max-w-2xl">
          <p className="text-xs font-medium uppercase tracking-widest text-accent">Hook Lab</p>
          <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
            Results
          </h1>
          <p className="mt-3 text-sm leading-relaxed text-slate-400">
            {isPolling
              ? 'Workflow still running — results refresh automatically when ready.'
              : 'Ranked hook variants, scores, and AI suggestions from your completed run.'}
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          {isPolling && (
            <span className="inline-flex items-center gap-2 rounded-lg border border-accent/30 bg-accent/10 px-3 py-2 text-xs text-accent">
              <Spinner className="h-3.5 w-3.5" />
              Polling…
            </span>
          )}
          <Button variant="secondary" onClick={() => void refetch()} loading={isFetching && !isPolling}>
            Refresh
          </Button>
          <Link to={`/runs/${id}`}>
            <Button variant="ghost">Status</Button>
          </Link>
          <Link to="/generate">
            <Button variant="ghost">New run</Button>
          </Link>
        </div>
      </header>

      {isPolling && !viewModel.hasDisplayableResults && (
        <div
          className="flex items-center gap-4 rounded-xl border border-accent/25 bg-accent/5 px-5 py-4"
          role="status"
          aria-live="polite"
        >
          <Spinner />
          <div>
            <p className="text-sm font-medium text-accent">Generating your hooks…</p>
            <p className="text-xs text-slate-500">
              Status: {viewModel.status} — this page updates every 2 seconds.
            </p>
          </div>
        </div>
      )}

      {isFailed && (
        <ErrorState
          error={viewModel.errorMessage ?? 'Workflow failed'}
          onRetry={() => void refetch()}
        />
      )}

      {showViewer && !isFailed && <ResultsViewer viewModel={viewModel} />}

      {isFailed && viewModel.hasDisplayableResults && (
        <ResultsViewer viewModel={viewModel} />
      )}
    </div>
  );
}
