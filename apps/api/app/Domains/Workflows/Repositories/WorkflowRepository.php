<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Repositories;

use App\Domains\Workflows\Contracts\WorkflowRepositoryContract;
use App\Domains\Workflows\Models\Workflow;
use InvalidArgumentException;

final class WorkflowRepository implements WorkflowRepositoryContract
{
    public function findBySlug(string $workspaceId, string $slug): ?Workflow
    {
        return Workflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function ensureBuiltIn(string $workspaceId, string $slug): Workflow
    {
        /** @var array<string, array{name: string, steps: list<array<string, mixed>>}> $definitions */
        $definitions = config('workflows.definitions', []);

        if (! isset($definitions[$slug])) {
            throw new InvalidArgumentException("Unknown built-in workflow slug [{$slug}].");
        }

        $definition = $definitions[$slug];

        return Workflow::query()->firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'slug' => $slug,
            ],
            [
                'name' => $definition['name'],
                'definition' => ['steps' => $definition['steps']],
                'version' => 1,
                'is_active' => true,
                'metadata' => ['built_in' => true],
            ],
        );
    }
}
