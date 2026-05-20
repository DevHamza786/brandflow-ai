import { useAutonomousFilters } from '@/features/autonomous/hooks/useAutonomousFilters';
import { useAutonomousDashboard } from '@/features/autonomous/hooks/useAutonomousDashboard';
import { AutonomousFilters } from '@/features/autonomous/components/filters/AutonomousFilters';
import { AutonomousOverviewCards } from '@/features/autonomous/components/kpi/AutonomousOverviewCards';
import { ConfidenceTrendChart } from '@/features/autonomous/components/charts/ConfidenceTrendChart';
import { DecisionOutcomesChart } from '@/features/autonomous/components/charts/DecisionOutcomesChart';
import { PostingDecisionChart } from '@/features/autonomous/components/charts/PostingDecisionChart';
import { DecisionTimeline } from '@/features/autonomous/components/sections/DecisionTimeline';
import { ExecutionPanels } from '@/features/autonomous/components/sections/ExecutionPanels';
import { ConfidenceThresholdControl } from '@/features/autonomous/components/indicators/ConfidenceThresholdControl';
import { OptimizationLearningIndicators } from '@/features/autonomous/components/indicators/OptimizationLearningIndicators';
import { AutonomousDashboardSkeleton } from '@/features/autonomous/components/feedback/AutonomousDashboardSkeleton';
import { AutonomousEmptyState } from '@/features/autonomous/components/feedback/AutonomousEmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { Button } from '@/shared/components/ui/Button';

export function AutonomousDashboardPage() {
  const { filters, setEngine, setStatus, setCycleRange } = useAutonomousFilters();
  const {
    view,
    workflowsQuery,
    runMutation,
    updateThresholdMutation,
    isInitialLoad,
    isEmpty,
    isFetching,
  } = useAutonomousDashboard(filters);

  if (isInitialLoad) {
    return (
      <div className="mx-auto w-full max-w-[90rem]">
        <AutonomousDashboardSkeleton />
      </div>
    );
  }

  if (workflowsQuery.isError) {
    return (
      <ErrorState
        error={workflowsQuery.error instanceof Error ? workflowsQuery.error.message : 'Failed to load'}
        onRetry={() => void workflowsQuery.refetch()}
      />
    );
  }

  return (
    <div className="mx-auto w-full max-w-[90rem] space-y-8 animate-fade-up">
      <header className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p className="text-xs font-medium uppercase tracking-widest text-sky-400">Autonomous intelligence</p>
            <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
              Autonomous posting engine
            </h1>
            <p className="mt-2 max-w-2xl text-sm text-slate-400">
              AI-native execution UX — confidence-gated decisions, optimization-aware signals, and future
              multi-agent publish loops (no live auto-publish in this release).
            </p>
          </div>
          <Button onClick={() => runMutation.mutate()} loading={runMutation.isPending}>
            Run evaluation cycle
          </Button>
        </div>
        <AutonomousFilters
          filters={filters}
          maxCycle={view.overview.current_cycle}
          onEngine={setEngine}
          onStatus={setStatus}
          onCycleRange={setCycleRange}
        />
        {isFetching && !isInitialLoad && (
          <p className="text-xs text-sky-400" role="status">
            Refreshing autonomous intelligence…
          </p>
        )}
      </header>

      {isEmpty ? (
        <AutonomousEmptyState onRun={() => runMutation.mutate()} running={runMutation.isPending} />
      ) : (
        <>
          <AutonomousOverviewCards overview={view.overview} loading={isFetching} />
          <div className="grid gap-4 xl:grid-cols-2">
            <ConfidenceTrendChart data={view.confidence_trends} />
            <DecisionOutcomesChart data={view.decision_outcomes} />
          </div>
          <div className="grid gap-4 lg:grid-cols-2">
            <PostingDecisionChart data={view.posting_decisions} />
            <ConfidenceThresholdControl
              minConfidence={view.overview.min_confidence}
              onApply={(v) => updateThresholdMutation.mutate(v)}
              applying={updateThresholdMutation.isPending}
            />
          </div>
          <div className="grid gap-4 xl:grid-cols-2">
            <ExecutionPanels timeline={view.timeline} />
            <OptimizationLearningIndicators adaptive={view.adaptive} />
          </div>
          <DecisionTimeline timeline={view.timeline} />
        </>
      )}
    </div>
  );
}
