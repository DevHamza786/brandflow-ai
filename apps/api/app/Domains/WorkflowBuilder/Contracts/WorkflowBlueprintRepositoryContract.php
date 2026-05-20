<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Contracts;

use App\Domains\WorkflowBuilder\Data\WorkflowBlueprintDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface WorkflowBlueprintRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): WorkflowBlueprintDto;

    public function findById(string $workspaceId, string $id): ?WorkflowBlueprintDto;

    public function findBySlug(string $workspaceId, string $slug, ?int $version = null): ?WorkflowBlueprintDto;

    /**
     * @return list<WorkflowBlueprintDto>
     */
    public function listActive(string $workspaceId): array;
}
