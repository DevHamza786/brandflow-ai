<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Data;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * Shared read model for all recommenders in a single generation pass.
 *
 * @param  list<PostPerformanceSnapshotDto>  $snapshots
 * @param  array<string, list<PostPerformanceSnapshotDto>>  $snapshotsByStyle
 * @param  list<array{hour:int,sample_count:int,avg_normalized:float}>  $postingHourHistogram
 * @param  array<string, mixed>  $personalizationBase
 */
final class RecommendationContextDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $lookbackDays,
        public readonly array $snapshots,
        public readonly float $baselineNormalized,
        public readonly float $baselineEngagementRate,
        public readonly float $p25Normalized,
        public readonly float $p75Normalized,
        public readonly array $snapshotsByStyle,
        public readonly array $postingHourHistogram,
        public readonly float $postsPerWeek,
        public readonly ?BrandProfileDto $brandProfile,
        public readonly array $personalizationBase,
    ) {
    }
}
