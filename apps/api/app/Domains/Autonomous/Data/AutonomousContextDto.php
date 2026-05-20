<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Data;

use App\Domains\Optimization\Data\OptimizationSnapshotDto;
use App\Domains\Recommendations\Data\RecommendationDto;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<RecommendationDto>  $recommendations
 * @param  list<OptimizationSnapshotDto>  $optimizationSnapshots
 * @param  array<string, mixed>  $analyticsSummary
 * @param  array<string, mixed>  $thresholds
 */
final class AutonomousContextDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly AutonomousWorkflowDto $workflow,
        public readonly array $recommendations,
        public readonly array $optimizationSnapshots,
        public readonly array $analyticsSummary,
        public readonly float $minConfidence,
        public readonly int $minRecommendationScore,
        public readonly array $thresholds,
    ) {
    }
}
