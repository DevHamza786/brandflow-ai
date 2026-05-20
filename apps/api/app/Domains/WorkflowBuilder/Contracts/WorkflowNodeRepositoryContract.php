<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Contracts;

use App\Domains\WorkflowBuilder\Data\WorkflowNodeDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface WorkflowNodeRepositoryContract extends WorkspaceScopedRepositoryContract
{
    /**
     * @return list<WorkflowNodeDto>
     */
    public function listByBlueprint(string $workspaceId, string $blueprintId): array;
}
