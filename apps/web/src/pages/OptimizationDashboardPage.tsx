import { useOptimizationFilters } from '@/features/optimization/hooks/useOptimizationFilters';
import { useOptimizationDashboard } from '@/features/optimization/hooks/useOptimizationDashboard';
import { OptimizationFilters } from '@/features/optimization/components/filters/OptimizationFilters';
import { OptimizationOverviewCards } from '@/features/optimization/components/kpi/OptimizationOverviewCards';
import { EngagementImprovementChart } from '@/features/optimization/components/charts/EngagementImprovementChart';
import { HookOptimizationTrendChart } from '@/features/optimization/components/charts/HookOptimizationTrendChart';
import { PostingTimeOptimizationChart } from '@/features/optimization/components/charts/PostingTimeOptimizationChart';
import { CtaEffectivenessChart } from '@/features/optimization/components/charts/CtaEffectivenessChart';
import { CycleHistoryChart } from '@/features/optimization/components/charts/CycleHistoryChart';
import { ExperimentComparisonChart } from '@/features/optimization/components/charts/ExperimentComparisonChart';
import { OptimizationRecommendationsPanel } from '@/features/optimization/components/sections/OptimizationRecommendationsPanel';
import { OptimizationOpportunitiesPanel } from '@/features/optimization/components/sections/OptimizationOpportunitiesPanel';
import { OptimizationHistoryTimeline } from '@/features/optimization/components/sections/OptimizationHistoryTimeline';
import { ExperimentComparisonPanel } from '@/features/optimization/components/sections/ExperimentComparisonPanel';
import { AdaptiveLearningIndicators } from '@/features/optimization/components/indicators/AdaptiveLearningIndicators';
import { OptimizationDashboardSkeleton } from '@/features/optimization/components/feedback/OptimizationDashboardSkeleton';
import { OptimizationEmptyState } from '@/features/optimization/components/feedback/OptimizationEmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { Button } from '@/shared/components/ui/Button';

export function OptimizationDashboardPage() {
  const {
    filters,
    setEngine,
    setCycleRange,
    setLookback,
    setComparison,
  } = useOptimizationFilters();

  const {
    view,
    loopsQuery,
    runCycleMutation,
    isInitialLoad,
    isEmpty,
    isFetching,
  } = useOptimizationDashboard(filters);

  if (isInitialLoad) {
    return (
      <div className="mx-auto w-full max-w-[90rem]">
        <OptimizationDashboardSkeleton />
      </div>
    );
  }

  if (loopsQuery.isError) {
    return (
      <ErrorState
        error={
          loopsQuery.error instanceof Error
            ? loopsQuery.error.message
            : 'Failed to load optimization loops'
        }
        onRetry={() => void loopsQuery.refetch()}
      />
    );
  }

  return (
    <div className="mx-auto w-full max-w-[90rem] space-y-8 animate-fade-up">
      <header className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p className="text-xs font-medium uppercase tracking-widest text-emerald-400">
              Adaptive intelligence
            </p>
            <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
              Optimization intelligence
            </h1>
            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-400">
              Analytics-first optimization UX — cycle history, engine uplifts, and
              recommendation-ready signals for self-improving content workflows.
            </p>
          </div>
          <Button
            className="shrink-0"
            onClick={() => runCycleMutation.mutate()}
            loading={runCycleMutation.isPending}
          >
            Run optimization cycle
          </Button>
        </div>

        <OptimizationFilters
          filters={filters}
          maxCycle={view.overview.current_cycle}
          onEngine={setEngine}
          onLookback={setLookback}
          onComparison={setComparison}
          onCycleRange={setCycleRange}
        />

        {isFetching && !isInitialLoad && (
          <p className="text-xs text-emerald-400" role="status">
            Refreshing optimization intelligence…
          </p>
        )}

        {runCycleMutation.isSuccess && (
          <p className="text-xs text-emerald-300" role="status">
            Cycle {runCycleMutation.data?.cycle_number} complete —{' '}
            {runCycleMutation.data?.snapshots_created} snapshots,{' '}
            {runCycleMutation.data?.recommendations_synced} recommendations synced.
          </p>
        )}
        {runCycleMutation.isError && (
          <p className="text-xs text-rose-400" role="alert">
            {runCycleMutation.error instanceof Error
              ? runCycleMutation.error.message
              : 'Cycle run failed'}
          </p>
        )}
      </header>

      {isEmpty ? (
        <OptimizationEmptyState
          onRunCycle={() => runCycleMutation.mutate()}
          running={runCycleMutation.isPending}
        />
      ) : (
        <>
          <OptimizationOverviewCards overview={view.overview} loading={isFetching} />

          <div className="grid gap-4 xl:grid-cols-2">
            <EngagementImprovementChart data={view.engagement_improvements} />
            <HookOptimizationTrendChart data={view.hook_trends} />
          </div>

          <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            <PostingTimeOptimizationChart data={view.posting_time_profile} />
            <CtaEffectivenessChart data={view.cta_effectiveness} />
            <div className="lg:col-span-2 xl:col-span-1">
              <CycleHistoryChart data={view.cycle_history} />
            </div>
          </div>

          <ExperimentComparisonChart data={view.experiments} />

          <div className="grid gap-4 xl:grid-cols-2">
            <ExperimentComparisonPanel rows={view.experiments} />
            <AdaptiveLearningIndicators status={view.adaptive} />
          </div>

          <div className="grid gap-4 xl:grid-cols-2">
            <OptimizationRecommendationsPanel recommendations={view.recommendations} />
            <OptimizationOpportunitiesPanel opportunities={view.opportunities} />
          </div>

          <OptimizationHistoryTimeline timeline={view.history_timeline} />
        </>
      )}
    </div>
  );
}
