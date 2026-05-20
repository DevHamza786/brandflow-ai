<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Providers;

use App\Domains\Recommendations\Actions\GenerateRecommendationsAction;
use App\Domains\Recommendations\Contracts\MlCompatibilityLayerContract;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Repositories\RecommendationRepository;
use App\Domains\Recommendations\Support\DefaultMlCompatibilityLayer;
use App\Domains\Recommendations\Services\AnalyticsCorrelationEngine;
use App\Domains\Recommendations\Services\AudienceFitRecommender;
use App\Domains\Recommendations\Services\CtaOptimizationRecommender;
use App\Domains\Recommendations\Services\HookOptimizationRecommender;
use App\Domains\Recommendations\Services\HookStyleCorrelationEngine;
use App\Domains\Recommendations\Services\OptimizationOpportunityDetector;
use App\Domains\Recommendations\Services\PostingTimeRecommender;
use App\Domains\Recommendations\Services\RecommendationAggregationService;
use App\Domains\Recommendations\Services\RecommendationEngine;
use App\Domains\Recommendations\Services\RecommendationExecutionLogger;
use App\Domains\Recommendations\Services\RecommendationOrchestrationService;
use App\Domains\Recommendations\Services\RecommendationQueryService;
use App\Domains\Recommendations\Services\RecommendationScoringService;
use App\Domains\Recommendations\Support\HookStyleClassifier;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class RecommendationsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Recommendations';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(RecommendationRepositoryContract::class, RecommendationRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(HookStyleClassifier::class);
        $this->app->singleton(RecommendationExecutionLogger::class);
        $this->app->singleton(AnalyticsCorrelationEngine::class);
        $this->app->singleton(HookStyleCorrelationEngine::class);
        $this->app->singleton(RecommendationScoringService::class);
        $this->app->singleton(RecommendationAggregationService::class);
        $this->app->singleton(HookOptimizationRecommender::class);
        $this->app->singleton(PostingTimeRecommender::class);
        $this->app->singleton(AudienceFitRecommender::class);
        $this->app->singleton(CtaOptimizationRecommender::class);
        $this->app->singleton(OptimizationOpportunityDetector::class);
        $this->app->singleton(RecommendationEngine::class);
        $this->app->singleton(RecommendationOrchestrationService::class);
        $this->app->singleton(RecommendationQueryService::class);
        $this->app->singleton(GenerateRecommendationsAction::class);
        $this->app->bind(MlCompatibilityLayerContract::class, DefaultMlCompatibilityLayer::class);
    }
}
