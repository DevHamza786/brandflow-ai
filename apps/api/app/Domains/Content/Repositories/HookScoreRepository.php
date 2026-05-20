<?php

declare(strict_types=1);

namespace App\Domains\Content\Repositories;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Models\HookScore;

final class HookScoreRepository implements HookScoreRepositoryContract
{
    public function persistFromCollection(
        string $workspaceId,
        string $contentVersionId,
        string $agentRunId,
        HookCollection $collection,
        string $model,
        string $promptVersion,
        ?string $traceId,
        array $metadata = [],
    ): HookScore {
        return HookScore::query()->create([
            'workspace_id' => $workspaceId,
            'content_version_id' => $contentVersionId,
            'agent_run_id' => $agentRunId,
            'score' => $collection->primary->overall,
            'dimensions' => $collection->primary->dimensions->toArray(),
            'variants' => array_map(
                static fn ($variant) => $variant->toArray(),
                $collection->variants
            ),
            'suggestions' => $collection->primary->suggestions,
            'model' => $model,
            'prompt_version' => $promptVersion,
            'trace_id' => $traceId,
            'metadata' => $metadata,
        ]);
    }

    public function findByAgentRun(string $workspaceId, string $agentRunId): ?HookScore
    {
        return HookScore::query()
            ->where('workspace_id', $workspaceId)
            ->where('agent_run_id', $agentRunId)
            ->latest('created_at')
            ->first();
    }
}
