<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Contracts;

use App\Domains\Coordination\Data\AgentCoordinationSnapshotDto;
use App\Domains\Coordination\Data\CreateCoordinationSnapshotDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AgentCoordinationSnapshotRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateCoordinationSnapshotDto $dto): AgentCoordinationSnapshotDto;

    public function findByIdempotencyKey(string $workspaceId, string $idempotencyKey): ?AgentCoordinationSnapshotDto;

    public function findById(string $workspaceId, string $id): ?AgentCoordinationSnapshotDto;

    /**
     * @return list<AgentCoordinationSnapshotDto>
     */
    public function listByCoordination(string $workspaceId, string $coordinationId, ?int $cycle = null): array;

    /**
     * @return list<AgentCoordinationSnapshotDto>
     */
    public function listRecent(string $workspaceId, int $limit = 50): array;
}
