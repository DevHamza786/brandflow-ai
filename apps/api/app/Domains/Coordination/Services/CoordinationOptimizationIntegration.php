<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Enums\OptimizationLoopType;

/**
 * Optimization-loop refs for coordinated agents.
 */
final class CoordinationOptimizationIntegration
{
    public function __construct(
        private readonly OptimizationLoopRepositoryContract $loops,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildContextRefs(string $workspaceId): array
    {
        $loop = $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite);

        return [
            'optimization_loop_id' => $loop->id,
            'current_cycle' => $loop->currentCycle,
            'ref_type' => 'optimization_loop',
        ];
    }
}
