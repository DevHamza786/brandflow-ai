<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Data\ExperimentDto;
use App\Domains\Experimentation\Models\Experiment;
use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Enums\OptimizationLoopType;

final class ExperimentOptimizationIntegration
{
    public function __construct(
        private readonly OptimizationLoopRepositoryContract $loops,
    ) {
    }

    public function linkLoop(string $workspaceId, ExperimentDto $experiment): ExperimentDto
    {
        if ($experiment->optimizationLoopId !== null) {
            return $experiment;
        }

        $loop = $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite);

        Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $experiment->id)
            ->update(['optimization_loop_id' => $loop->id]);

        return $experiment;
    }
}
