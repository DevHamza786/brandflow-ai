<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Events;

use App\Domains\WorkflowBuilder\Data\ExecuteBlueprintResultDto;
use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowBlueprintExecuted
{
    use Dispatchable;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $blueprintId,
        public readonly ExecuteBlueprintResultDto $result,
    ) {
    }
}
