<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;

/**
 * Posting-time intelligence foundation (hour-of-day aggregates).
 */
final class PostingTimeAnalyzer
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<array{hour:int,sample_count:int,avg_normalized:float}>
     */
    public function hourlyEngagementProfile(string $workspaceId, int $daysBack = 30): array
    {
        return $this->snapshots->postingHourHistogram($workspaceId, $daysBack);
    }
}
