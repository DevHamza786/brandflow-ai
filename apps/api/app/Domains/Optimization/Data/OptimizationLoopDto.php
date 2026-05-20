<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Data;

use App\Domains\Optimization\Enums\OptimizationLoopStatus;
use App\Domains\Optimization\Enums\OptimizationLoopType;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $mlState
 * @param  array<string, mixed>  $metadata
 */
final class OptimizationLoopDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly OptimizationLoopType $loopType,
        public readonly OptimizationLoopStatus $status,
        public readonly string $correlationKey,
        public readonly int $currentCycle,
        public readonly array $config,
        public readonly array $mlState,
        public readonly array $metadata,
        public readonly CarbonInterface $startedAt,
        public readonly ?CarbonInterface $lastRunAt,
        public readonly ?CarbonInterface $completedAt,
    ) {
    }
}
