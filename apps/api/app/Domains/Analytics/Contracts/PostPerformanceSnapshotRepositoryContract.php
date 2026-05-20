<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Contracts;

use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface PostPerformanceSnapshotRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreatePostPerformanceSnapshotDto $dto): PostPerformanceSnapshotDto;

    public function findById(string $workspaceId, string $id): ?PostPerformanceSnapshotDto;

    /**
     * @return list<PostPerformanceSnapshotDto>
     */
    public function topByNormalizedEngagement(string $workspaceId, int $limit = 20): array;

    /**
     * Hour-of-day (0–23) histogram from `posted_at` for posting-time foundation.
     *
     * @return list<array{hour:int,sample_count:int,avg_normalized:float}>
     */
    public function postingHourHistogram(string $workspaceId, int $daysBack = 30): array;

    /**
     * Snapshots observed within [from, to] (inclusive), oldest first.
     *
     * @return list<PostPerformanceSnapshotDto>
     */
    public function listObservedBetween(
        string $workspaceId,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        int $limit = 5000,
    ): array;

    /**
     * Recent snapshots for recommendation / correlation engines.
     *
     * @return list<PostPerformanceSnapshotDto>
     */
    public function listRecentForWorkspace(
        string $workspaceId,
        int $daysBack = 90,
        int $limit = 500,
    ): array;
}
