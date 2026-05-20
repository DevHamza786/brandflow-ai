<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Contracts;

use App\Domains\WorkflowBuilder\Data\WorkflowEdgeDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface WorkflowEdgeRepositoryContract extends WorkspaceScopedRepositoryContract
{
    /**
     * @return list<WorkflowEdgeDto>
     */
    public function listByBlueprint(string $workspaceId, string $blueprintId): array;
}
