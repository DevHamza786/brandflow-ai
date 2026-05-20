<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<string>  $executedNodeKeys
 * @param  list<string>  $skippedNodeKeys
 * @param  list<string>  $failedNodeKeys
 */
final class ExecuteBlueprintResultDto extends DataTransferObject
{
    public function __construct(
        public readonly string $blueprintId,
        public readonly ?string $workflowRunId,
        public readonly int $nodesExecuted,
        public readonly array $executedNodeKeys,
        public readonly array $skippedNodeKeys,
        public readonly array $failedNodeKeys,
        public readonly string $traceId,
    ) {
    }
}
