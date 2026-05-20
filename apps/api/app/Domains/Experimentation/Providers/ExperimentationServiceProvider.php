<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Providers;

use App\Domains\Experimentation\Actions\AssignExperimentVariantAction;
use App\Domains\Experimentation\Actions\CompareExperimentAction;
use App\Domains\Experimentation\Contracts\ExperimentRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentResultRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentVariantRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentationMlCompatibilityLayerContract;
use App\Domains\Experimentation\Repositories\ExperimentRepository;
use App\Domains\Experimentation\Repositories\ExperimentResultRepository;
use App\Domains\Experimentation\Repositories\ExperimentVariantRepository;
use App\Domains\Experimentation\Services\ExperimentAnalyticsIntegration;
use App\Domains\Experimentation\Services\ExperimentExecutionLogger;
use App\Domains\Experimentation\Services\ExperimentOptimizationIntegration;
use App\Domains\Experimentation\Services\ExperimentQueryService;
use App\Domains\Experimentation\Services\ExperimentRecommendationIntegration;
use App\Domains\Experimentation\Services\ExperimentScoringEngine;
use App\Domains\Experimentation\Services\ExperimentationEngine;
use App\Domains\Experimentation\Services\StatisticalComparisonEngine;
use App\Domains\Experimentation\Services\VariantAssignmentEngine;
use App\Domains\Experimentation\Support\DefaultExperimentationMlLayer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class ExperimentationServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Experimentation';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(ExperimentRepositoryContract::class, ExperimentRepository::class);
        $this->app->bind(ExperimentVariantRepositoryContract::class, ExperimentVariantRepository::class);
        $this->app->bind(ExperimentResultRepositoryContract::class, ExperimentResultRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ExperimentExecutionLogger::class);
        $this->app->singleton(ExperimentAnalyticsIntegration::class);
        $this->app->singleton(ExperimentOptimizationIntegration::class);
        $this->app->singleton(ExperimentRecommendationIntegration::class);
        $this->app->singleton(ExperimentScoringEngine::class);
        $this->app->singleton(StatisticalComparisonEngine::class);
        $this->app->singleton(VariantAssignmentEngine::class);
        $this->app->singleton(ExperimentationEngine::class);
        $this->app->singleton(ExperimentQueryService::class);
        $this->app->singleton(AssignExperimentVariantAction::class);
        $this->app->singleton(CompareExperimentAction::class);
        $this->app->bind(ExperimentationMlCompatibilityLayerContract::class, DefaultExperimentationMlLayer::class);
    }
}
