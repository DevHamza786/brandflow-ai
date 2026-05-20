import { useMemo } from 'react';
import { normalizeHookResults } from '@/features/results/lib/normalizeHookResults';
import type { HookResultsViewModel } from '@/features/results/types/results.types';
import { useAgentRunDetail } from '@/features/workflow/hooks/useAgentRunDetail';
import { useAgentRunResults } from '@/features/workflow/hooks/useAgentRunResults';
import type { ApiError } from '@/shared/types/api';

export function useHookResults(agentRunId: string | undefined) {
  const resultsQuery = useAgentRunResults(agentRunId);
  const detailQuery = useAgentRunDetail(agentRunId);

  const viewModel: HookResultsViewModel | null = useMemo(
    () => normalizeHookResults(agentRunId ?? '', resultsQuery.data, detailQuery.data),
    [agentRunId, resultsQuery.data, detailQuery.data],
  );

  const isInitialLoading =
    Boolean(agentRunId) &&
    !viewModel &&
    (resultsQuery.isLoading || detailQuery.isLoading);

  const isError = resultsQuery.isError || detailQuery.isError;
  const error = (resultsQuery.error ?? detailQuery.error) as ApiError | null;

  const refetch = async () => {
    await Promise.all([resultsQuery.refetch(), detailQuery.refetch()]);
  };

  return {
    viewModel,
    isInitialLoading,
    isPolling: viewModel?.isPolling ?? false,
    isFetching: resultsQuery.isFetching || detailQuery.isFetching,
    isError,
    error,
    refetch,
    resultsQuery,
    detailQuery,
  };
}
