<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\WorkflowBuilder\Enums\WorkflowEdgeType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>|null  $condition
 * @param  array<string, mixed>  $metadata
 */
final class WorkflowEdgeDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $workflowBlueprintId,
        public readonly string $fromNodeKey,
        public readonly string $toNodeKey,
        public readonly WorkflowEdgeType $edgeType,
        public readonly ?array $condition,
        public readonly array $metadata,
    ) {
    }
}
