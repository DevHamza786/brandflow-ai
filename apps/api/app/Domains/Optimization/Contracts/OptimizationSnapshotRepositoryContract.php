<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Contracts;

use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationSnapshotDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface OptimizationSnapshotRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateOptimizationSnapshotDto $dto): OptimizationSnapshotDto;

    public function findById(string $workspaceId, string $id): ?OptimizationSnapshotDto;

    /**
     * @return list<OptimizationSnapshotDto>
     */
    public function listByLoop(string $workspaceId, string $loopId, int $limit = 50): array;

    /**
     * @return list<OptimizationSnapshotDto>
     */
    public function listRecent(string $workspaceId, int $limit = 50): array;
}
