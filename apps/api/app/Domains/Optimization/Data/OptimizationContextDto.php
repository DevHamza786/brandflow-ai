<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Data;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<PostPerformanceSnapshotDto>  $currentPeriodSnapshots
 * @param  list<PostPerformanceSnapshotDto>  $previousPeriodSnapshots
 * @param  array<string, mixed>  $personalizationBase
 */
final class OptimizationContextDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $lookbackDays,
        public readonly int $comparisonDays,
        public readonly array $currentPeriodSnapshots,
        public readonly array $previousPeriodSnapshots,
        public readonly float $currentAvgNormalized,
        public readonly float $previousAvgNormalized,
        public readonly float $currentAvgEngagementRate,
        public readonly float $previousAvgEngagementRate,
        public readonly ?BrandProfileDto $brandProfile,
        public readonly array $personalizationBase,
    ) {
    }
}
