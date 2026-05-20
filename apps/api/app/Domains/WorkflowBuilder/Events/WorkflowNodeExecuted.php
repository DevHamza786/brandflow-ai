<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowNodeExecuted
{
    use Dispatchable;

    /**
     * @param  array{status: string, payload: array<string, mixed>}  $result
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $blueprintId,
        public readonly string $nodeKey,
        public readonly array $result,
    ) {
    }
}
