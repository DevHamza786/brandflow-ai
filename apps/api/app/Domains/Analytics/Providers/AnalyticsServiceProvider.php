<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Providers;

use App\Domains\Analytics\Contracts\AnalyticsEventRepositoryContract;
use App\Domains\Analytics\Contracts\EngagementMetricRepositoryContract;
use App\Domains\Analytics\Contracts\FeatureVectorBuilderContract;
use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Listeners\IngestHookScoredAnalyticsListener;
use App\Domains\Analytics\Repositories\AnalyticsEventRepository;
use App\Domains\Analytics\Repositories\EngagementMetricRepository;
use App\Domains\Analytics\Repositories\PostPerformanceSnapshotRepository;
use App\Domains\Analytics\Support\DefaultFeatureVectorBuilder;
use App\Domains\Analytics\Services\AnalyticsDashboardService;
use App\Domains\Analytics\Services\AnalyticsEventIngestionService;
use App\Domains\Analytics\Services\AnalyticsExecutionLogger;
use App\Domains\Analytics\Services\AnalyticsOrchestrationService;
use App\Domains\Analytics\Services\AnalyticsQueryService;
use App\Domains\Analytics\Services\BestPerformingVariantAnalyzer;
use App\Domains\Analytics\Services\EngagementNormalizationService;
use App\Domains\Analytics\Services\EngagementTrackingService;
use App\Domains\Analytics\Services\HookPerformanceScoringEngine;
use App\Domains\Analytics\Services\PerformanceAggregationService;
use App\Domains\Analytics\Services\PostingTimeAnalyzer;
use App\Domains\Analytics\Services\RecommendationSignalsService;
use App\Domains\Content\Events\HookScored;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\Event;

final class AnalyticsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Analytics';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(AnalyticsEventRepositoryContract::class, AnalyticsEventRepository::class);
        $this->app->bind(PostPerformanceSnapshotRepositoryContract::class, PostPerformanceSnapshotRepository::class);
        $this->app->bind(EngagementMetricRepositoryContract::class, EngagementMetricRepository::class);
        $this->app->bind(FeatureVectorBuilderContract::class, DefaultFeatureVectorBuilder::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(AnalyticsExecutionLogger::class);
        $this->app->singleton(EngagementNormalizationService::class);
        $this->app->singleton(HookPerformanceScoringEngine::class);
        $this->app->singleton(PerformanceAggregationService::class);
        $this->app->singleton(AnalyticsEventIngestionService::class);
        $this->app->singleton(EngagementTrackingService::class);
        $this->app->singleton(BestPerformingVariantAnalyzer::class);
        $this->app->singleton(PostingTimeAnalyzer::class);
        $this->app->singleton(RecommendationSignalsService::class);
        $this->app->singleton(AnalyticsQueryService::class);
        $this->app->singleton(AnalyticsOrchestrationService::class);
        $this->app->singleton(AnalyticsDashboardService::class);
    }

    public function boot(): void
    {
        Event::listen(HookScored::class, [IngestHookScoredAnalyticsListener::class, 'handle']);
    }
}
