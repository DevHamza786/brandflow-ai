<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\UpdateWritingSampleDto;
use App\Domains\Brand\Data\WritingSampleDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface WritingSampleRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?WritingSampleDto;

    /**
     * @return list<WritingSampleDto>
     */
    public function listByWorkspace(string $workspaceId, int $limit = 20): array;

    /**
     * @return list<WritingSampleDto>
     */
    public function listByProfile(string $workspaceId, string $brandProfileId, int $limit = 50): array;

    /**
     * @return list<WritingSampleDto>
     */
    public function listEmbeddingReady(string $workspaceId, int $limit = 50): array;

    public function create(CreateWritingSampleDto $dto, array $normalizedStyleData): WritingSampleDto;

    public function update(string $workspaceId, string $id, UpdateWritingSampleDto $dto, array $normalizedStyleData): WritingSampleDto;

    public function delete(string $workspaceId, string $id): void;

    public function markEmbeddingReady(string $workspaceId, string $id, bool $ready = true): WritingSampleDto;
}
