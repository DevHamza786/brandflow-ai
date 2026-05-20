import { useAnalyticsDashboard } from '@/features/analytics/hooks/useAnalyticsDashboard';
import { useDashboardFilters } from '@/features/analytics/hooks/useDashboardFilters';
import { DashboardFilters } from '@/features/analytics/components/filters/DashboardFilters';
import { KpiGrid } from '@/features/analytics/components/kpi/KpiGrid';
import { EngagementChart } from '@/features/analytics/components/charts/EngagementChart';
import { ScoreTrendChart } from '@/features/analytics/components/charts/ScoreTrendChart';
import { PostingFrequencyChart } from '@/features/analytics/components/charts/PostingFrequencyChart';
import { PostingTimeChart } from '@/features/analytics/components/charts/PostingTimeChart';
import { AudienceEngagementChart } from '@/features/analytics/components/charts/AudienceEngagementChart';
import { HookPerformanceChart } from '@/features/analytics/components/charts/HookPerformanceChart';
import { PerformanceSummaryCards } from '@/features/analytics/components/sections/PerformanceSummaryCards';
import { TopHooksSection } from '@/features/analytics/components/sections/TopHooksSection';
import { DashboardSkeleton } from '@/features/analytics/components/feedback/DashboardSkeleton';
import { AnalyticsEmptyState } from '@/features/analytics/components/feedback/AnalyticsEmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';

export function AnalyticsDashboardPage() {
  const { filters, queryParams, setPreset, setCustomRange } = useDashboardFilters();
  const query = useAnalyticsDashboard(queryParams);

  const isInitialLoad = query.isLoading && !query.data;
  const isEmpty =
    query.data != null &&
    query.data.kpis.posts_observed === 0 &&
    query.data.engagement_series.every((p) => p.posts === 0);

  if (isInitialLoad) {
    return (
      <div className="mx-auto w-full max-w-[90rem]">
        <DashboardSkeleton />
      </div>
    );
  }

  if (query.isError) {
    return (
      <div className="mx-auto max-w-3xl">
        <ErrorState
          error={query.error instanceof Error ? query.error.message : 'Failed to load analytics'}
          onRetry={() => void query.refetch()}
        />
      </div>
    );
  }

  const data = query.data!;

  return (
    <div className="mx-auto w-full max-w-[90rem] space-y-8 animate-fade-up">
      <header className="space-y-4">
        <div>
          <p className="text-xs font-medium uppercase tracking-widest text-accent">BrandFlow AI</p>
          <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
            Analytics dashboard
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-400">
            Performance intelligence from post snapshots and Hook Lab — optimized for AI insights and
            future recommendation signals.
          </p>
        </div>
        <DashboardFilters
          filters={filters}
          rangeLabel={data.range.label}
          onPreset={setPreset}
          onCustomRange={setCustomRange}
        />
        {query.isFetching && !query.isLoading && (
          <p className="text-xs text-accent" role="status">
            Refreshing metrics…
          </p>
        )}
      </header>

      {isEmpty ? (
        <AnalyticsEmptyState />
      ) : (
        <>
          <KpiGrid kpis={data.kpis} comparison={data.comparison} loading={query.isFetching && !query.data} />
          <PerformanceSummaryCards kpis={data.kpis} />

          <div className="grid gap-4 xl:grid-cols-2">
            <EngagementChart data={data.engagement_series} />
            <AudienceEngagementChart overview={data.audience_overview} />
          </div>

          <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            <div className="lg:col-span-1 xl:col-span-2">
              <ScoreTrendChart data={data.score_trend} />
            </div>
            <PostingFrequencyChart data={data.posting_frequency} />
          </div>

          <div className="grid gap-4 xl:grid-cols-2">
            <PostingTimeChart data={data.posting_time} />
            {data.top_hooks.length > 0 ? (
              <HookPerformanceChart hooks={data.top_hooks} />
            ) : (
              <div className="flex items-center justify-center rounded-xl border border-dashed border-border px-6 py-16 text-sm text-slate-500">
                Hook performance chart appears when snapshots include hook metadata.
              </div>
            )}
          </div>

          <TopHooksSection hooks={data.top_hooks} />
        </>
      )}
    </div>
  );
}
