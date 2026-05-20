<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Compiled blueprint graph for orchestration.
 *
 * @param  list<WorkflowNodeDto>  $nodes
 * @param  list<WorkflowEdgeDto>  $edges
 * @param  list<string>  $entryNodeKeys
 */
final class WorkflowGraphDto extends DataTransferObject
{
    public function __construct(
        public readonly WorkflowBlueprintDto $blueprint,
        public readonly array $nodes,
        public readonly array $edges,
        public readonly array $entryNodeKeys,
    ) {
    }
}
