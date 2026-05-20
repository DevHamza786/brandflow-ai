<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;
use App\Domains\Workflows\Contracts\WorkflowRepositoryContract;
use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;
use App\Domains\Workflows\Repositories\WorkflowRepository;
use App\Domains\Workflows\Repositories\WorkflowRunRepository;

final class WorkflowsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Workflows';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(WorkflowRepositoryContract::class, WorkflowRepository::class);
        $this->app->bind(WorkflowRunRepositoryContract::class, WorkflowRunRepository::class);
    }
}
