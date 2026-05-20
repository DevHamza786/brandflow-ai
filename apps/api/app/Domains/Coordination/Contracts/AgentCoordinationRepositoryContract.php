<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Contracts;

use App\Domains\Coordination\Data\AgentCoordinationDto;
use App\Domains\Coordination\Enums\CoordinationMode;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AgentCoordinationRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): AgentCoordinationDto;

    public function findById(string $workspaceId, string $id): ?AgentCoordinationDto;

    public function incrementCycle(string $workspaceId, string $coordinationId): AgentCoordinationDto;

    public function updateSharedContext(
        string $workspaceId,
        string $coordinationId,
        array $sharedContext,
    ): AgentCoordinationDto;

    public function tryAcquireLock(string $workspaceId, string $coordinationId, string $lockToken): bool;

    public function releaseLock(string $workspaceId, string $coordinationId, string $lockToken): void;

    public function updateStatus(string $workspaceId, string $coordinationId, string $status): AgentCoordinationDto;

    /**
     * @return list<AgentCoordinationDto>
     */
    public function listRecent(string $workspaceId, int $limit = 10): array;
}
