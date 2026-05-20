<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Providers;

use App\Domains\WorkflowBuilder\Actions\ExecuteWorkflowBlueprintAction;
use App\Domains\WorkflowBuilder\Contracts\WorkflowBlueprintRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowBuilderMlCompatibilityLayerContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowEdgeRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowNodeRepositoryContract;
use App\Domains\WorkflowBuilder\Repositories\WorkflowBlueprintRepository;
use App\Domains\WorkflowBuilder\Repositories\WorkflowEdgeRepository;
use App\Domains\WorkflowBuilder\Repositories\WorkflowNodeRepository;
use App\Domains\WorkflowBuilder\Services\NodeExecutionEngine;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderAnalyticsIntegration;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderEngine;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderOptimizationIntegration;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderQueryService;
use App\Domains\WorkflowBuilder\Services\WorkflowExecutionLogger;
use App\Domains\WorkflowBuilder\Services\WorkflowGraphOrchestrator;
use App\Domains\WorkflowBuilder\Services\WorkflowValidationEngine;
use App\Domains\WorkflowBuilder\Support\DefaultWorkflowBuilderMlLayer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class WorkflowBuilderServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'WorkflowBuilder';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(WorkflowBlueprintRepositoryContract::class, WorkflowBlueprintRepository::class);
        $this->app->bind(WorkflowNodeRepositoryContract::class, WorkflowNodeRepository::class);
        $this->app->bind(WorkflowEdgeRepositoryContract::class, WorkflowEdgeRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(WorkflowExecutionLogger::class);
        $this->app->singleton(WorkflowBuilderAnalyticsIntegration::class);
        $this->app->singleton(WorkflowBuilderOptimizationIntegration::class);
        $this->app->singleton(WorkflowValidationEngine::class);
        $this->app->singleton(WorkflowGraphOrchestrator::class);
        $this->app->singleton(NodeExecutionEngine::class);
        $this->app->singleton(WorkflowBuilderEngine::class);
        $this->app->singleton(WorkflowBuilderQueryService::class);
        $this->app->singleton(ExecuteWorkflowBlueprintAction::class);
        $this->app->bind(WorkflowBuilderMlCompatibilityLayerContract::class, DefaultWorkflowBuilderMlLayer::class);
    }
}
