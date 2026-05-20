<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Contracts\OptimizationSnapshotRepositoryContract;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Data\OptimizationSnapshotDto;

final class OptimizationQueryService
{
    public function __construct(
        private readonly OptimizationLoopRepositoryContract $loops,
        private readonly OptimizationSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<OptimizationLoopDto>
     */
    public function listLoops(string $workspaceId, int $limit = 20): array
    {
        return $this->loops->listActive($workspaceId, $limit);
    }

    public function findLoop(string $workspaceId, string $loopId): ?OptimizationLoopDto
    {
        return $this->loops->findById($workspaceId, $loopId);
    }

    /**
     * @return list<OptimizationSnapshotDto>
     */
    public function listSnapshotsByLoop(string $workspaceId, string $loopId, int $limit = 50): array
    {
        return $this->snapshots->listByLoop($workspaceId, $loopId, $limit);
    }

    public function findSnapshot(string $workspaceId, string $snapshotId): ?OptimizationSnapshotDto
    {
        return $this->snapshots->findById($workspaceId, $snapshotId);
    }

    /**
     * @return list<OptimizationSnapshotDto>
     */
    public function listRecentSnapshots(string $workspaceId, int $limit = 50): array
    {
        return $this->snapshots->listRecent($workspaceId, $limit);
    }
}
