<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\WorkflowBuilder\Enums\WorkflowNodeType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $position
 */
final class WorkflowNodeDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $workflowBlueprintId,
        public readonly string $nodeKey,
        public readonly WorkflowNodeType $nodeType,
        public readonly ?string $label,
        public readonly array $config,
        public readonly array $position,
        public readonly int $sortOrder,
    ) {
    }
}
