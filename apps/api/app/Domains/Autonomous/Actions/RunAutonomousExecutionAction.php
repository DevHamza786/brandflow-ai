<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Actions;

use App\Domains\Autonomous\Data\RunAutonomousExecutionResultDto;
use App\Domains\Autonomous\Services\AutonomousOrchestrationService;

final class RunAutonomousExecutionAction
{
    public function __construct(
        private readonly AutonomousOrchestrationService $orchestration,
    ) {
    }

    public function execute(string $workspaceId): RunAutonomousExecutionResultDto
    {
        return $this->orchestration->runWorkspaceCycle($workspaceId);
    }
}
