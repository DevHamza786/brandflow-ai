<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Providers;

use App\Domains\Autonomous\Actions\RunAutonomousExecutionAction;
use App\Domains\Autonomous\Actions\UpdateAutonomousWorkflowAction;
use App\Domains\Autonomous\Contracts\AutonomousExecutionSnapshotRepositoryContract;
use App\Domains\Autonomous\Contracts\AutonomousMlCompatibilityLayerContract;
use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Repositories\AutonomousExecutionSnapshotRepository;
use App\Domains\Autonomous\Repositories\AutonomousWorkflowRepository;
use App\Domains\Autonomous\Services\AutonomousAnalyticsIntegration;
use App\Domains\Autonomous\Services\AutonomousExecutionLogger;
use App\Domains\Autonomous\Services\AutonomousOptimizationIntegration;
use App\Domains\Autonomous\Services\AutonomousOrchestrationService;
use App\Domains\Autonomous\Services\AutonomousPostingEngine;
use App\Domains\Autonomous\Services\AutonomousQueryService;
use App\Domains\Autonomous\Services\AutonomousSchedulerService;
use App\Domains\Autonomous\Services\ContentSelectionEngine;
use App\Domains\Autonomous\Services\PostingDecisionEngine;
use App\Domains\Autonomous\Services\PostingTimeDecisionEngine;
use App\Domains\Autonomous\Services\RecommendationConfidenceEngine;
use App\Domains\Autonomous\Support\DefaultAutonomousMlLayer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class AutonomousServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Autonomous';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(AutonomousWorkflowRepositoryContract::class, AutonomousWorkflowRepository::class);
        $this->app->bind(AutonomousExecutionSnapshotRepositoryContract::class, AutonomousExecutionSnapshotRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(AutonomousExecutionLogger::class);
        $this->app->singleton(RecommendationConfidenceEngine::class);
        $this->app->singleton(AutonomousAnalyticsIntegration::class);
        $this->app->singleton(AutonomousOptimizationIntegration::class);
        $this->app->singleton(PostingTimeDecisionEngine::class);
        $this->app->singleton(ContentSelectionEngine::class);
        $this->app->singleton(PostingDecisionEngine::class);
        $this->app->singleton(AutonomousPostingEngine::class);
        $this->app->singleton(AutonomousOrchestrationService::class);
        $this->app->singleton(AutonomousSchedulerService::class);
        $this->app->singleton(AutonomousQueryService::class);
        $this->app->singleton(RunAutonomousExecutionAction::class);
        $this->app->singleton(UpdateAutonomousWorkflowAction::class);
        $this->app->bind(AutonomousMlCompatibilityLayerContract::class, DefaultAutonomousMlLayer::class);
    }
}
