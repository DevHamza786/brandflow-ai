<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Contracts;

use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationLoopType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface OptimizationLoopRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findOrCreateActive(string $workspaceId, OptimizationLoopType $type): OptimizationLoopDto;

    public function findById(string $workspaceId, string $id): ?OptimizationLoopDto;

    public function incrementCycle(string $workspaceId, string $loopId): OptimizationLoopDto;

    /**
     * @return list<OptimizationLoopDto>
     */
    public function listActive(string $workspaceId, int $limit = 20): array;
}
