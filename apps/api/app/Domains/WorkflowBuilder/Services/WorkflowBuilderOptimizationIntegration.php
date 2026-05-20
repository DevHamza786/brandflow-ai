<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Enums\OptimizationLoopType;

final class WorkflowBuilderOptimizationIntegration
{
    public function __construct(
        private readonly OptimizationLoopRepositoryContract $loops,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function executionContextRefs(string $workspaceId): array
    {
        $loop = $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite);

        return [
            'optimization_loop_id' => $loop->id,
            'ref_type' => 'optimization',
        ];
    }
}
