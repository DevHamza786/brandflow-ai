import { useMemo } from 'react';
import { Link } from 'react-router-dom';
import { useAgentRunResults } from '@/features/workflow/hooks/useAgentRunResults';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { LoadingState } from '@/shared/components/feedback/LoadingState';
import { Badge } from '@/shared/components/ui/Badge';
import { Button } from '@/shared/components/ui/Button';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { useToast } from '@/shared/providers/ToastProvider';
import type { AgentRunResults, ApiError, HookVariant } from '@/shared/types/api';

function collectVariants(results: AgentRunResults | undefined): HookVariant[] {
  if (!results) return [];
  if (results.variants.length > 0) {
    return [...results.variants].sort((a, b) => b.overall - a.overall);
  }
  const nested = results.outputs.flatMap((o) => o.variants);
  return [...nested].sort((a, b) => b.overall - a.overall);
}

function formatResultsError(error: Record<string, unknown> | null): string {
  if (!error) return 'Hook generation failed.';
  const detail = error.detail;
  if (typeof detail === 'string') return detail;
  const title = error.title;
  if (typeof title === 'string') return title;
  try {
    return JSON.stringify(error);
  } catch {
    return 'Hook generation failed.';
  }
}

type Props = {
  agentRunId: string | null;
};

export function GeneratedHooksPanel({ agentRunId }: Props) {
  const toast = useToast();
  const { data: results, isLoading, isError, error, refetch, isFetching } = useAgentRunResults(
    agentRunId ?? undefined,
  );

  const variants = useMemo(() => collectVariants(results), [results]);
  const status = results?.status;

  const handleCopy = async (text: string) => {
    try {
      await navigator.clipboard.writeText(text);
      toast.push('Copied to clipboard', 'success');
    } catch {
      toast.push('Could not copy — try selecting the text manually', 'error');
    }
  };

  const body = (() => {
    if (!agentRunId) {
      return (
        <EmptyState
          title="No hooks yet"
          description="Submit the form to generate ranked hooks. Results stream here when the AI queue finishes."
        />
      );
    }

    if (isLoading && !results) {
      return <LoadingState message="Loading run status…" />;
    }

    if (isError) {
      return (
        <ErrorState
          error={(error as ApiError) ?? new Error('Could not load results')}
          onRetry={() => void refetch()}
        />
      );
    }

    if (isInFlightWorkflowStatus(status)) {
      return (
        <LoadingState
          className="py-10"
          message={
            status === 'queued'
              ? 'Queued on the AI worker — hooks will appear here when scoring finishes.'
              : 'Scoring and generating variants…'
          }
        />
      );
    }

    if (status === 'failed') {
      return <ErrorState error={formatResultsError(results?.error ?? null)} onRetry={() => void refetch()} />;
    }

    if (variants.length === 0) {
      return (
        <EmptyState
          title="No variants returned"
          description="The run completed but no hook lines were in the response. Open the full run for details."
        />
      );
    }

    return (
      <ul className="space-y-4">
        {variants.map((variant, index) => (
          <li
            key={`${variant.experiment_variant ?? 'v'}-${index}`}
            className="rounded-lg border border-border/80 bg-surface-overlay/40 p-4"
          >
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div className="min-w-0 flex-1 space-y-2">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    #{index + 1}
                  </span>
                  <Badge className="border-border/80 text-slate-300">
                    Score {variant.overall.toFixed(1)}
                  </Badge>
                </div>
                <p className="whitespace-pre-wrap text-sm leading-relaxed text-slate-100">{variant.text}</p>
              </div>
              <Button
                type="button"
                variant="secondary"
                className="shrink-0"
                onClick={() => void handleCopy(variant.text)}
              >
                Copy
              </Button>
            </div>
          </li>
        ))}
      </ul>
    );
  })();

  return (
    <Card className="border-border/80">
      <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h2 className="text-sm font-medium text-slate-300">Generated hooks</h2>
          <p className="mt-1 text-xs text-slate-500">
            Ranked variants from the latest run. Copy any line for your post.
          </p>
        </div>
        {agentRunId && (
          <div className="flex flex-wrap items-center gap-2">
            {isFetching && !isLoading ? (
              <span className="text-xs text-slate-500">Updating…</span>
            ) : null}
            <Link
              to={`/runs/${agentRunId}`}
              className="text-xs font-medium text-accent hover:underline"
            >
              Open full workflow view
            </Link>
          </div>
        )}
      </CardHeader>
      <CardBody>{body}</CardBody>
    </Card>
  );
}
