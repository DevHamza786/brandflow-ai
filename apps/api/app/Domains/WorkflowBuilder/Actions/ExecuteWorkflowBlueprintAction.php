<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Actions;

use App\Domains\WorkflowBuilder\Data\ExecuteBlueprintResultDto;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderEngine;

final class ExecuteWorkflowBlueprintAction
{
    public function __construct(
        private readonly WorkflowBuilderEngine $engine,
    ) {
    }

    public function execute(string $workspaceId, ?string $blueprintId = null): ExecuteBlueprintResultDto
    {
        if ($blueprintId !== null) {
            return $this->engine->execute($workspaceId, $blueprintId);
        }

        return $this->engine->executeDefault($workspaceId);
    }
}
