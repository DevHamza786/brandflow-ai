<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface CompetitorSnapshotRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?CompetitorSnapshotDto;

    public function findLatestByCompetitor(string $workspaceId, string $competitorId): ?CompetitorSnapshotDto;

    public function existsByContentHash(string $workspaceId, string $competitorId, string $contentHash): bool;

    public function createFromNormalized(array $attributes): CompetitorSnapshotDto;

    public function updateAnalytics(string $workspaceId, string $snapshotId, array $analytics): CompetitorSnapshotDto;

    /**
     * @return list<CompetitorSnapshotDto>
     */
    public function listRecentByCompetitor(string $workspaceId, string $competitorId, int $limit = 10): array;
}
