<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\WorkflowBuilder\Enums\BlueprintStatus;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $mlState
 * @param  array<string, mixed>  $metadata
 */
final class WorkflowBlueprintDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $slug,
        public readonly string $name,
        public readonly BlueprintStatus $status,
        public readonly int $version,
        public readonly bool $isActive,
        public readonly string $blueprintType,
        public readonly array $config,
        public readonly array $mlState,
        public readonly array $metadata,
    ) {
    }
}
