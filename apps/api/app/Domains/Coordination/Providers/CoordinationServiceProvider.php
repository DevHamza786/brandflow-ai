<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Providers;

use App\Domains\Coordination\Actions\RunCoordinationCycleAction;
use App\Domains\Coordination\Contracts\AgentCoordinationRepositoryContract;
use App\Domains\Coordination\Contracts\AgentCoordinationSnapshotRepositoryContract;
use App\Domains\Coordination\Contracts\CoordinationMlCompatibilityLayerContract;
use App\Domains\Coordination\Repositories\AgentCoordinationRepository;
use App\Domains\Coordination\Repositories\AgentCoordinationSnapshotRepository;
use App\Domains\Coordination\Services\AgentContextOrchestrator;
use App\Domains\Coordination\Services\AgentMemorySynchronization;
use App\Domains\Coordination\Services\AgentPriorityEngine;
use App\Domains\Coordination\Services\AgentRoutingEngine;
use App\Domains\Coordination\Services\CoordinationAnalyticsIntegration;
use App\Domains\Coordination\Services\CoordinationExecutionLogger;
use App\Domains\Coordination\Services\CoordinationOptimizationIntegration;
use App\Domains\Coordination\Services\CoordinationQueryService;
use App\Domains\Coordination\Services\InterAgentCommunicationLayer;
use App\Domains\Coordination\Services\MultiAgentCoordinator;
use App\Domains\Coordination\Services\WorkflowSharingEngine;
use App\Domains\Coordination\Support\DefaultCoordinationMlLayer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class CoordinationServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Coordination';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(AgentCoordinationRepositoryContract::class, AgentCoordinationRepository::class);
        $this->app->bind(AgentCoordinationSnapshotRepositoryContract::class, AgentCoordinationSnapshotRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(CoordinationExecutionLogger::class);
        $this->app->singleton(WorkflowSharingEngine::class);
        $this->app->singleton(CoordinationAnalyticsIntegration::class);
        $this->app->singleton(CoordinationOptimizationIntegration::class);
        $this->app->singleton(AgentMemorySynchronization::class);
        $this->app->singleton(AgentRoutingEngine::class);
        $this->app->singleton(AgentPriorityEngine::class);
        $this->app->singleton(AgentContextOrchestrator::class);
        $this->app->singleton(InterAgentCommunicationLayer::class);
        $this->app->singleton(MultiAgentCoordinator::class);
        $this->app->singleton(CoordinationQueryService::class);
        $this->app->singleton(RunCoordinationCycleAction::class);
        $this->app->bind(CoordinationMlCompatibilityLayerContract::class, DefaultCoordinationMlLayer::class);
    }
}
