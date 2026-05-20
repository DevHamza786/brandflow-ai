<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Data;

use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $mlState
 * @param  array<string, mixed>  $metadata
 */
final class AutonomousWorkflowDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly AutonomousWorkflowStatus $status,
        public readonly AutonomousWorkflowMode $mode,
        public readonly string $correlationKey,
        public readonly int $currentCycle,
        public readonly ?string $optimizationLoopId,
        public readonly ?string $workflowRunId,
        public readonly array $config,
        public readonly array $mlState,
        public readonly array $metadata,
        public readonly bool $manualOverrideEnabled,
        public readonly bool $autonomousExecutionEnabled,
        public readonly ?CarbonInterface $lockedAt,
        public readonly ?string $lockToken,
        public readonly CarbonInterface $startedAt,
        public readonly ?CarbonInterface $lastRunAt,
        public readonly ?CarbonInterface $completedAt,
    ) {
    }
}
