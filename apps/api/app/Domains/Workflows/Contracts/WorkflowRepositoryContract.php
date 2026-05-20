<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Contracts;

use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;
use App\Domains\Workflows\Models\Workflow;

interface WorkflowRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findBySlug(string $workspaceId, string $slug): ?Workflow;

    public function ensureBuiltIn(string $workspaceId, string $slug): Workflow;
}
