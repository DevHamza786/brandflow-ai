<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Actions;

use App\Domains\Optimization\Data\RunOptimizationCycleResultDto;
use App\Domains\Optimization\Services\OptimizationOrchestrationService;

final class RunOptimizationCycleAction
{
    public function __construct(
        private readonly OptimizationOrchestrationService $orchestration,
    ) {
    }

    public function execute(
        string $workspaceId,
        ?int $lookbackDays = null,
        ?int $comparisonDays = null,
    ): RunOptimizationCycleResultDto {
        return $this->orchestration->runWorkspaceCycle($workspaceId, $lookbackDays, $comparisonDays);
    }
}
