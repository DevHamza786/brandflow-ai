import { useMemo } from 'react';
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  fetchAutonomousSnapshots,
  fetchAutonomousWorkflows,
  runAutonomousExecution,
  updateAutonomousWorkflow,
} from '@/features/autonomous/api/autonomous.api';
import { buildAutonomousDashboard } from '@/features/autonomous/lib/dashboardModel';
import { autonomousKeys } from '@/features/autonomous/hooks/autonomousQueryKeys';
import type { AutonomousFilterState } from '@/features/autonomous/types/dashboard';

export function useAutonomousDashboard(filters: AutonomousFilterState) {
  const queryClient = useQueryClient();

  const workflowsQuery = useQuery({
    queryKey: autonomousKeys.workflows(),
    queryFn: fetchAutonomousWorkflows,
    staleTime: 60_000,
  });

  const workflow = workflowsQuery.data?.[0] ?? null;

  const snapshotsQuery = useQuery({
    queryKey: autonomousKeys.snapshots(workflow?.id),
    queryFn: () => fetchAutonomousSnapshots(workflow?.id),
    enabled: workflowsQuery.isSuccess,
    staleTime: 45_000,
    placeholderData: keepPreviousData,
  });

  const view = useMemo(
    () =>
      buildAutonomousDashboard(workflow, snapshotsQuery.data ?? [], filters),
    [workflow, snapshotsQuery.data, filters],
  );

  const runMutation = useMutation({
    mutationFn: runAutonomousExecution,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: autonomousKeys.all }),
  });

  const updateThresholdMutation = useMutation({
    mutationFn: (minConfidence: number) => {
      if (!workflow) {
        throw new Error('No workflow');
      }
      return updateAutonomousWorkflow(workflow.id, {
        min_confidence: minConfidence,
        config: { ...workflow.config, min_confidence: minConfidence },
      });
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: autonomousKeys.all }),
  });

  const isInitialLoad =
    (workflowsQuery.isLoading && !workflowsQuery.data) ||
    (snapshotsQuery.isLoading && !snapshotsQuery.data);

  const isEmpty = view.overview.current_cycle === 0 && view.overview.total_snapshots === 0;

  return {
    view,
    workflowsQuery,
    snapshotsQuery,
    runMutation,
    updateThresholdMutation,
    isInitialLoad,
    isEmpty,
    isFetching: workflowsQuery.isFetching || snapshotsQuery.isFetching,
  };
}
