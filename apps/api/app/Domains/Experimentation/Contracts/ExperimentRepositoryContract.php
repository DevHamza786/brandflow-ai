<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Contracts;

use App\Domains\Experimentation\Data\ExperimentDto;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface ExperimentRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findOrCreateByType(string $workspaceId, ExperimentType $type): ExperimentDto;

    public function findById(string $workspaceId, string $id): ?ExperimentDto;

    public function findBySlug(string $workspaceId, string $slug): ?ExperimentDto;

    public function markRunning(string $workspaceId, string $experimentId): ExperimentDto;

    /**
     * @return list<ExperimentDto>
     */
    public function listRunning(string $workspaceId, int $limit = 20): array;
}
