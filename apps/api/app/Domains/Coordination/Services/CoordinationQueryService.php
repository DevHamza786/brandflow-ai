<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Contracts\AgentCoordinationRepositoryContract;
use App\Domains\Coordination\Contracts\AgentCoordinationSnapshotRepositoryContract;
use App\Domains\Coordination\Data\AgentCoordinationDto;
use App\Domains\Coordination\Data\AgentCoordinationSnapshotDto;

final class CoordinationQueryService
{
    public function __construct(
        private readonly AgentCoordinationRepositoryContract $coordinations,
        private readonly AgentCoordinationSnapshotRepositoryContract $snapshots,
    ) {
    }

    public function defaultCoordination(string $workspaceId): AgentCoordinationDto
    {
        return $this->coordinations->findOrCreateDefault($workspaceId);
    }

    public function findCoordination(string $workspaceId, string $id): ?AgentCoordinationDto
    {
        return $this->coordinations->findById($workspaceId, $id);
    }

    /**
     * @return list<AgentCoordinationDto>
     */
    public function listCoordinations(string $workspaceId): array
    {
        return $this->coordinations->listRecent($workspaceId);
    }

    public function findSnapshot(string $workspaceId, string $id): ?AgentCoordinationSnapshotDto
    {
        return $this->snapshots->findById($workspaceId, $id);
    }

    /**
     * @return list<AgentCoordinationSnapshotDto>
     */
    public function listSnapshots(string $workspaceId, ?string $coordinationId = null, ?int $cycle = null): array
    {
        if ($coordinationId !== null) {
            return $this->snapshots->listByCoordination($workspaceId, $coordinationId, $cycle);
        }

        return $this->snapshots->listRecent($workspaceId);
    }
}
