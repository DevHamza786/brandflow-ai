<?php

declare(strict_types=1);

namespace App\Domains\Content\Contracts;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Content\Models\HookScore;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface HookScoreRepositoryContract extends WorkspaceScopedRepositoryContract
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function persistFromCollection(
        string $workspaceId,
        string $contentVersionId,
        string $agentRunId,
        HookCollection $collection,
        string $model,
        string $promptVersion,
        ?string $traceId,
        array $metadata = [],
    ): HookScore;

    public function findByAgentRun(string $workspaceId, string $agentRunId): ?HookScore;
}
