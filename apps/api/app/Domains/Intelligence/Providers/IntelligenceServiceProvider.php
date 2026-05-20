<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers;

use App\Domains\Intelligence\Actions\CreateCompetitorAction;
use App\Domains\Intelligence\Actions\IngestCompetitorSnapshotAction;
use App\Domains\Intelligence\Contracts\CompetitorMlCompatibilityLayerContract;
use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Repositories\CompetitorRepository;
use App\Domains\Intelligence\Repositories\CompetitorSnapshotRepository;
use App\Domains\Intelligence\Services\CompetitorAnalyticsService;
use App\Domains\Intelligence\Services\CompetitorIngestionService;
use App\Domains\Intelligence\Services\CompetitorOrchestrationService;
use App\Domains\Intelligence\Services\CompetitorQueryService;
use App\Domains\Intelligence\Services\CompetitorRecommendationBridge;
use App\Domains\Intelligence\Services\CompetitorScoringEngine;
use App\Domains\Intelligence\Services\CompetitorTrendAnalysisService;
use App\Domains\Intelligence\Services\CompetitorExecutionLogger;
use App\Domains\Intelligence\Services\EngagementBenchmarkingEngine;
use App\Domains\Intelligence\Services\HookPatternExtractionEngine;
use App\Domains\Intelligence\Services\PostingFrequencyAnalyzer;
use App\Domains\Intelligence\Support\CompetitorPayloadNormalizer;
use App\Domains\Intelligence\Support\DefaultCompetitorMlCompatibilityLayer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class IntelligenceServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Intelligence';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(CompetitorRepositoryContract::class, CompetitorRepository::class);
        $this->app->bind(CompetitorSnapshotRepositoryContract::class, CompetitorSnapshotRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(CompetitorExecutionLogger::class);
        $this->app->singleton(CompetitorPayloadNormalizer::class);
        $this->app->singleton(HookPatternExtractionEngine::class);
        $this->app->singleton(PostingFrequencyAnalyzer::class);
        $this->app->singleton(EngagementBenchmarkingEngine::class);
        $this->app->singleton(CompetitorTrendAnalysisService::class);
        $this->app->singleton(CompetitorScoringEngine::class);
        $this->app->singleton(CompetitorAnalyticsService::class);
        $this->app->singleton(CompetitorIngestionService::class);
        $this->app->singleton(CompetitorRecommendationBridge::class);
        $this->app->singleton(CompetitorOrchestrationService::class);
        $this->app->singleton(CompetitorQueryService::class);
        $this->app->singleton(CreateCompetitorAction::class);
        $this->app->singleton(IngestCompetitorSnapshotAction::class);
        $this->app->bind(CompetitorMlCompatibilityLayerContract::class, DefaultCompetitorMlCompatibilityLayer::class);
    }
}
