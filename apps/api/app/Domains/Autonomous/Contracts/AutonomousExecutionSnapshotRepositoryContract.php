<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Contracts;

use App\Domains\Autonomous\Data\AutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AutonomousExecutionSnapshotRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateAutonomousExecutionSnapshotDto $dto): AutonomousExecutionSnapshotDto;

    public function existsByIdempotencyKey(string $idempotencyKey): bool;

    public function findById(string $workspaceId, string $id): ?AutonomousExecutionSnapshotDto;

    /**
     * @return list<AutonomousExecutionSnapshotDto>
     */
    public function listByWorkflow(string $workspaceId, string $workflowId, int $limit = 100): array;

    /**
     * @return list<AutonomousExecutionSnapshotDto>
     */
    public function listRecent(string $workspaceId, int $limit = 100): array;
}
