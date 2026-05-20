<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Autonomous\Data\RunAutonomousExecutionResultDto;

final class AutonomousOrchestrationService
{
    public function __construct(
        private readonly AutonomousPostingEngine $engine,
        private readonly AutonomousExecutionLogger $logger,
    ) {
    }

    public function runWorkspaceCycle(string $workspaceId): RunAutonomousExecutionResultDto
    {
        $this->logger->info('orchestration_dispatch', ['workspace_id' => $workspaceId]);

        return $this->engine->runCycle($workspaceId);
    }
}
