<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Data;

use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $baselineMetrics
 * @param  array<string, mixed>  $observedMetrics
 * @param  array<string, mixed>  $deltaMetrics
 * @param  array<string, mixed>  $evidence
 * @param  array<string, mixed>  $actionPayload
 * @param  array<string, mixed>  $personalizationContext
 * @param  array<string, mixed>  $mlFeatures
 */
final class CreateOptimizationSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $optimizationLoopId,
        public readonly int $cycleNumber,
        public readonly string $engine,
        public readonly string $focus,
        public readonly string $title,
        public readonly string $summary,
        public readonly ?string $rationale,
        public readonly int $score,
        public readonly ?float $confidence,
        public readonly OptimizationSnapshotStatus $status,
        public readonly array $baselineMetrics,
        public readonly array $observedMetrics,
        public readonly array $deltaMetrics,
        public readonly array $evidence,
        public readonly array $actionPayload,
        public readonly array $personalizationContext,
        public readonly array $mlFeatures = [],
        public readonly ?CarbonInterface $capturedAt = null,
        public readonly ?string $idempotencyKey = null,
    ) {
    }
}
