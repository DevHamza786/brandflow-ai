<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Contracts;

use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\UpdateAutonomousWorkflowDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AutonomousWorkflowRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): AutonomousWorkflowDto;

    public function findById(string $workspaceId, string $id): ?AutonomousWorkflowDto;

    /**
     * @return list<AutonomousWorkflowDto>
     */
    public function listActive(string $workspaceId, int $limit = 20): array;

    public function incrementCycle(string $workspaceId, string $workflowId): AutonomousWorkflowDto;

    public function update(string $workspaceId, string $workflowId, UpdateAutonomousWorkflowDto $dto): AutonomousWorkflowDto;

    public function tryAcquireLock(string $workspaceId, string $workflowId, string $lockToken): bool;

    public function releaseLock(string $workspaceId, string $workflowId, string $lockToken): void;
}
