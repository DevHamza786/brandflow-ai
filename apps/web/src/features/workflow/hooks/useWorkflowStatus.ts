import { useMemo } from 'react';
import { useAgentRunDetail } from '@/features/workflow/hooks/useAgentRunDetail';
import { useAgentRunResults } from '@/features/workflow/hooks/useAgentRunResults';
import { useWorkflowStatusToasts } from '@/features/workflow/hooks/useWorkflowStatusToasts';
import { buildWorkflowExecutionState } from '@/features/workflow/lib/buildWorkflowExecutionState';
import type { WorkflowExecutionState } from '@/features/workflow/types/workflow.types';
import type { ApiError } from '@/shared/types/api';

export function useWorkflowStatus(agentRunId: string | undefined) {
  const detailQuery = useAgentRunDetail(agentRunId);
  const resultsQuery = useAgentRunResults(agentRunId);

  const execution: WorkflowExecutionState | null = useMemo(() => {
    if (!agentRunId) return null;
    return buildWorkflowExecutionState(agentRunId, detailQuery.data, resultsQuery.data);
  }, [agentRunId, detailQuery.data, resultsQuery.data]);

  const status = execution?.status ?? 'queued';
  useWorkflowStatusToasts(agentRunId, status);

  const isInitialLoading =
    Boolean(agentRunId) &&
    !detailQuery.data &&
    !resultsQuery.data &&
    (detailQuery.isLoading || resultsQuery.isLoading);

  const isError = detailQuery.isError || resultsQuery.isError;
  const error = (detailQuery.error ?? resultsQuery.error) as ApiError | null;

  const refetch = async () => {
    await Promise.all([detailQuery.refetch(), resultsQuery.refetch()]);
  };

  const isFetching = detailQuery.isFetching || resultsQuery.isFetching;

  return {
    execution,
    status,
    isPolling: execution?.isPolling ?? false,
    isInitialLoading,
    isError,
    error,
    isFetching,
    refetch,
    detailQuery,
    resultsQuery,
  };
}

export { useAgentRunDetail, useAgentRunResults };
