<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Coordination\Enums\CoordinationMode;
use App\Domains\Coordination\Enums\CoordinationStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $sharedContext
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $mlState
 * @param  array<string, mixed>  $metadata
 */
final class AgentCoordinationDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly CoordinationStatus $status,
        public readonly CoordinationMode $coordinationMode,
        public readonly string $correlationKey,
        public readonly int $currentCycle,
        public readonly ?string $workflowRunId,
        public readonly ?string $workflowBlueprintId,
        public readonly ?string $optimizationLoopId,
        public readonly ?string $autonomousWorkflowId,
        public readonly array $sharedContext,
        public readonly array $config,
        public readonly array $mlState,
        public readonly array $metadata,
        public readonly CarbonInterface $startedAt,
        public readonly ?CarbonInterface $lastRunAt,
        public readonly ?CarbonInterface $completedAt,
    ) {
    }
}
