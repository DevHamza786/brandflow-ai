<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Providers;

use App\Domains\Optimization\Actions\RunOptimizationCycleAction;
use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Contracts\OptimizationMlCompatibilityLayerContract;
use App\Domains\Optimization\Contracts\OptimizationSnapshotRepositoryContract;
use App\Domains\Optimization\Repositories\OptimizationLoopRepository;
use App\Domains\Optimization\Repositories\OptimizationSnapshotRepository;
use App\Domains\Optimization\Services\AudienceFitOptimizationEngine;
use App\Domains\Optimization\Services\CtaOptimizationEngine;
use App\Domains\Optimization\Services\HookOptimizationEngine;
use App\Domains\Optimization\Services\OptimizationAnalyticsIntegration;
use App\Domains\Optimization\Services\OptimizationEngine;
use App\Domains\Optimization\Services\OptimizationExecutionLogger;
use App\Domains\Optimization\Services\OptimizationOrchestrationService;
use App\Domains\Optimization\Services\OptimizationQueryService;
use App\Domains\Optimization\Services\OptimizationRecommendationBridge;
use App\Domains\Optimization\Services\OptimizationScoringService;
use App\Domains\Optimization\Services\PostingTimeOptimizationEngine;
use App\Domains\Optimization\Support\DefaultOptimizationMlLayer;
use App\Domains\Optimization\Support\HistoricalComparisonSupport;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class OptimizationServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Optimization';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(OptimizationLoopRepositoryContract::class, OptimizationLoopRepository::class);
        $this->app->bind(OptimizationSnapshotRepositoryContract::class, OptimizationSnapshotRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(HistoricalComparisonSupport::class);
        $this->app->singleton(OptimizationExecutionLogger::class);
        $this->app->singleton(OptimizationScoringService::class);
        $this->app->singleton(OptimizationAnalyticsIntegration::class);
        $this->app->singleton(HookOptimizationEngine::class);
        $this->app->singleton(PostingTimeOptimizationEngine::class);
        $this->app->singleton(CtaOptimizationEngine::class);
        $this->app->singleton(AudienceFitOptimizationEngine::class);
        $this->app->singleton(OptimizationRecommendationBridge::class);
        $this->app->singleton(OptimizationEngine::class);
        $this->app->singleton(OptimizationOrchestrationService::class);
        $this->app->singleton(OptimizationQueryService::class);
        $this->app->singleton(RunOptimizationCycleAction::class);
        $this->app->bind(OptimizationMlCompatibilityLayerContract::class, DefaultOptimizationMlLayer::class);
    }
}
