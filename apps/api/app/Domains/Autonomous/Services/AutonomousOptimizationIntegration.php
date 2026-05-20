<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Contracts\OptimizationSnapshotRepositoryContract;
use App\Domains\Optimization\Enums\OptimizationLoopType;

/**
 * Optimization-loop signals for autonomous posting decisions.
 */
final class AutonomousOptimizationIntegration
{
    public function __construct(
        private readonly OptimizationLoopRepositoryContract $loops,
        private readonly OptimizationSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<\App\Domains\Optimization\Data\OptimizationSnapshotDto>
     */
    public function latestSnapshots(string $workspaceId, int $limit = 20): array
    {
        $loop = $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite);

        return $this->snapshots->listByLoop($workspaceId, $loop->id, $limit);
    }

    public function resolveOptimizationLoopId(string $workspaceId): string
    {
        return $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite)->id;
    }
}
