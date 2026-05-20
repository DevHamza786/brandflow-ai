import { useCompetitorDashboard } from '@/features/competitors/hooks/useCompetitorDashboard';
import { CompetitorSelector } from '@/features/competitors/components/filters/CompetitorSelector';
import { CompetitorOverviewCards } from '@/features/competitors/components/kpi/CompetitorOverviewCards';
import { EngagementComparisonChart } from '@/features/competitors/components/charts/EngagementComparisonChart';
import { CompetitorPostingFrequencyChart } from '@/features/competitors/components/charts/CompetitorPostingFrequencyChart';
import { HookPatternChart } from '@/features/competitors/components/charts/HookPatternChart';
import { CtaPatternChart } from '@/features/competitors/components/charts/CtaPatternChart';
import { CompetitorTrendChart } from '@/features/competitors/components/charts/CompetitorTrendChart';
import { BenchmarkComparisonPanel } from '@/features/competitors/components/sections/BenchmarkComparisonPanel';
import { TrendDetectionPanel } from '@/features/competitors/components/sections/TrendDetectionPanel';
import { TopCompetitorPostsSection } from '@/features/competitors/components/sections/TopCompetitorPostsSection';
import { CompetitorRecommendationsSection } from '@/features/competitors/components/sections/CompetitorRecommendationsSection';
import { CompetitorDashboardSkeleton } from '@/features/competitors/components/feedback/CompetitorDashboardSkeleton';
import {
  CompetitorEmptyState,
  CompetitorNoSnapshotState,
} from '@/features/competitors/components/feedback/CompetitorEmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';

export function CompetitorDashboardPage() {
  const {
    competitors,
    selectedId,
    setSelectedId,
    listQuery,
    intelligenceQuery,
    recommendationsQuery,
    report,
    recommendations,
    topPosts,
    hasSnapshots,
  } = useCompetitorDashboard();

  const isInitialLoad = listQuery.isLoading && competitors.length === 0;

  if (isInitialLoad) {
    return (
      <div className="mx-auto w-full max-w-[90rem]">
        <CompetitorDashboardSkeleton />
      </div>
    );
  }

  if (listQuery.isError) {
    return (
      <ErrorState
        error={listQuery.error instanceof Error ? listQuery.error.message : 'Failed to load competitors'}
        onRetry={() => void listQuery.refetch()}
      />
    );
  }

  if (competitors.length === 0) {
    return (
      <div className="mx-auto w-full max-w-[90rem] animate-fade-up">
        <header className="mb-8">
          <p className="text-xs font-medium uppercase tracking-widest text-violet-400">Intelligence</p>
          <h1 className="mt-2 text-2xl font-semibold text-white sm:text-3xl">Competitor dashboard</h1>
        </header>
        <CompetitorEmptyState />
      </div>
    );
  }

  const loadingIntel = intelligenceQuery.isLoading && !report;
  const competitorName = report?.competitor.name ?? 'Competitor';

  return (
    <div className="mx-auto w-full max-w-[90rem] space-y-8 animate-fade-up">
      <header className="space-y-4">
        <div>
          <p className="text-xs font-medium uppercase tracking-widest text-violet-400">BrandFlow AI</p>
          <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
            Competitor intelligence
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-400">
            Analytics-first competitive insights — hook patterns, benchmarks, and recommendation-ready
            signals for adaptive content strategy.
          </p>
        </div>
        <CompetitorSelector
          competitors={competitors}
          selectedId={selectedId}
          onSelect={setSelectedId}
        />
        {(intelligenceQuery.isFetching || recommendationsQuery.isFetching) && !loadingIntel && (
          <p className="text-xs text-accent" role="status">
            Refreshing intelligence…
          </p>
        )}
      </header>

      {intelligenceQuery.isError ? (
        <ErrorState
          error={
            intelligenceQuery.error instanceof Error
              ? intelligenceQuery.error.message
              : 'Failed to load competitor report'
          }
          onRetry={() => void intelligenceQuery.refetch()}
        />
      ) : (
        <>
          <CompetitorOverviewCards report={report} loading={loadingIntel} />

          {!hasSnapshots && !loadingIntel && report && (
            <CompetitorNoSnapshotState name={competitorName} />
          )}

          {report?.latest_snapshot && hasSnapshots && (
            <>
              <BenchmarkComparisonPanel benchmark={report.benchmark} />

              <div className="grid gap-4 xl:grid-cols-2">
                <EngagementComparisonChart benchmark={report.benchmark} />
                <CompetitorPostingFrequencyChart snapshot={report.latest_snapshot} />
              </div>

              <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                <div className="lg:col-span-1 xl:col-span-2">
                  <HookPatternChart styles={report.latest_snapshot.hook_patterns.styles ?? []} />
                </div>
                <CtaPatternChart snapshot={report.latest_snapshot} />
              </div>

              <div className="grid gap-4 lg:grid-cols-2">
                <CompetitorTrendChart
                  trends={report.trends}
                  currentScore={report.intelligence_score}
                />
                <TrendDetectionPanel trends={report.trends} />
              </div>

              <CompetitorRecommendationsSection
                recommendations={recommendations}
                insights={report.hook_pattern_insights}
                hints={report.recommendation_hints}
              />

              <TopCompetitorPostsSection posts={topPosts} />
            </>
          )}
        </>
      )}
    </div>
  );
}
