<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Data\RunAutonomousExecutionResultDto;

/**
 * Cron entry — evaluates active workflows when scheduler is enabled (no publish).
 */
final class AutonomousSchedulerService
{
    public function __construct(
        private readonly AutonomousWorkflowRepositoryContract $workflows,
        private readonly AutonomousOrchestrationService $orchestration,
        private readonly AutonomousExecutionLogger $logger,
    ) {
    }

    /**
     * @return list<RunAutonomousExecutionResultDto>
     */
    public function processDueWorkspaces(?string $workspaceId = null): array
    {
        if (! (bool) config('autonomous.scheduler_enabled', false)) {
            $this->logger->info('scheduler_skipped', ['reason' => 'disabled']);

            return [];
        }

        $results = [];
        if ($workspaceId !== null) {
            $results[] = $this->orchestration->runWorkspaceCycle($workspaceId);

            return $results;
        }

        $devId = (string) config('pbos.dev_workspace_id');
        if ($devId !== '') {
            $this->workflows->findOrCreateDefault($devId);
            $results[] = $this->orchestration->runWorkspaceCycle($devId);
        }

        return $results;
    }
}
