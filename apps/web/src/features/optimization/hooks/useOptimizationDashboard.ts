import { useMemo } from 'react';
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchAnalyticsDashboard } from '@/features/analytics/api/analytics.api';
import { analyticsKeys } from '@/features/analytics/hooks/analyticsQueryKeys';
import {
  fetchOptimizationLoops,
  fetchOptimizationRecommendations,
  fetchOptimizationSnapshots,
  runOptimizationCycle,
} from '@/features/optimization/api/optimization.api';
import { buildOptimizationDashboard } from '@/features/optimization/lib/dashboardModel';
import { optimizationKeys } from '@/features/optimization/hooks/optimizationQueryKeys';
import type { OptimizationFilterState } from '@/features/optimization/types/dashboard';

export function useOptimizationDashboard(filters: OptimizationFilterState) {
  const queryClient = useQueryClient();

  const loopsQuery = useQuery({
    queryKey: optimizationKeys.loops(),
    queryFn: fetchOptimizationLoops,
    staleTime: 60_000,
  });

  const compositeLoop = loopsQuery.data?.find((l) => l.loop_type === 'composite') ?? loopsQuery.data?.[0];

  const snapshotsQuery = useQuery({
    queryKey: optimizationKeys.snapshots(compositeLoop?.id),
    queryFn: () => fetchOptimizationSnapshots(compositeLoop?.id),
    enabled: loopsQuery.isSuccess,
    staleTime: 45_000,
    placeholderData: keepPreviousData,
  });

  const recommendationsQuery = useQuery({
    queryKey: optimizationKeys.recommendations(),
    queryFn: fetchOptimizationRecommendations,
    staleTime: 60_000,
  });

  const analyticsQuery = useQuery({
    queryKey: analyticsKeys.dashboard({ preset: '30d' }),
    queryFn: () => fetchAnalyticsDashboard({ preset: '30d' }),
    staleTime: 120_000,
  });

  const engagementDelta = analyticsQuery.data?.comparison.engagement_rate_delta ?? null;

  const view = useMemo(() => {
    return buildOptimizationDashboard(
      loopsQuery.data ?? [],
      snapshotsQuery.data ?? [],
      recommendationsQuery.data ?? [],
      filters,
      engagementDelta,
    );
  }, [
    loopsQuery.data,
    snapshotsQuery.data,
    recommendationsQuery.data,
    filters,
    engagementDelta,
  ]);

  const runCycleMutation = useMutation({
    mutationFn: () =>
      runOptimizationCycle({
        lookback_days: filters.lookbackDays,
        comparison_days: filters.comparisonDays,
      }),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: optimizationKeys.all }),
        queryClient.invalidateQueries({ queryKey: ['recommendations'] }),
        queryClient.invalidateQueries({ queryKey: analyticsKeys.all }),
      ]);
    },
  });

  const isInitialLoad =
    (loopsQuery.isLoading && !loopsQuery.data) ||
    (snapshotsQuery.isLoading && !snapshotsQuery.data);

  const isEmpty =
    view.loop == null ||
    (view.overview.current_cycle === 0 && view.overview.total_snapshots === 0);

  const isFetching =
    loopsQuery.isFetching ||
    snapshotsQuery.isFetching ||
    recommendationsQuery.isFetching;

  return {
    view,
    loopsQuery,
    snapshotsQuery,
    recommendationsQuery,
    analyticsQuery,
    runCycleMutation,
    isInitialLoad,
    isEmpty,
    isFetching,
    hasLoop: view.loop != null,
  };
}
