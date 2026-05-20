<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Autonomous\Contracts\AutonomousExecutionSnapshotRepositoryContract;
use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Data\AutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;

final class AutonomousQueryService
{
    public function __construct(
        private readonly AutonomousWorkflowRepositoryContract $workflows,
        private readonly AutonomousExecutionSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<AutonomousWorkflowDto>
     */
    public function listWorkflows(string $workspaceId): array
    {
        return $this->workflows->listActive($workspaceId);
    }

    public function findWorkflow(string $workspaceId, string $id): ?AutonomousWorkflowDto
    {
        return $this->workflows->findById($workspaceId, $id);
    }

    public function defaultWorkflow(string $workspaceId): AutonomousWorkflowDto
    {
        return $this->workflows->findOrCreateDefault($workspaceId);
    }

    /**
     * @return list<AutonomousExecutionSnapshotDto>
     */
    public function listSnapshots(string $workspaceId, ?string $workflowId = null): array
    {
        if ($workflowId !== null) {
            return $this->snapshots->listByWorkflow($workspaceId, $workflowId);
        }

        return $this->snapshots->listRecent($workspaceId);
    }

    public function findSnapshot(string $workspaceId, string $id): ?AutonomousExecutionSnapshotDto
    {
        return $this->snapshots->findById($workspaceId, $id);
    }
}
