<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Data\RunOptimizationCycleResultDto;

/**
 * Entry point for workflow / cron / API to run optimization cycles.
 */
final class OptimizationOrchestrationService
{
    public function __construct(
        private readonly OptimizationEngine $engine,
        private readonly OptimizationExecutionLogger $logger,
    ) {
    }

    public function runWorkspaceCycle(
        string $workspaceId,
        ?int $lookbackDays = null,
        ?int $comparisonDays = null,
    ): RunOptimizationCycleResultDto {
        $this->logger->info('orchestration_dispatch', [
            'workspace_id' => $workspaceId,
            'lookback_days' => $lookbackDays,
            'comparison_days' => $comparisonDays,
        ]);

        return $this->engine->runCycle($workspaceId, $lookbackDays, $comparisonDays);
    }
}
