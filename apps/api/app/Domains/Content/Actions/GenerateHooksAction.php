<?php

declare(strict_types=1);

namespace App\Domains\Content\Actions;

use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Jobs\RunHookAgentJob;
use App\Domains\Agents\Models\AgentRun;
use Illuminate\Support\Facades\Log;

/**
 * Queues a HookAgent run for a content version.
 */
final class GenerateHooksAction
{
    public function __construct(
        private readonly AgentRunRepositoryContract $agentRuns,
    ) {
    }

    public function execute(
        string $workspaceId,
        HookAgentConfig $config,
        ?string $idempotencyKey = null,
    ): AgentRun {
        $run = $this->agentRuns->createQueued(
            workspaceId: $workspaceId,
            slug: 'hook',
            input: [
                'content_version_id' => $config->contentVersionId,
            ],
            options: [
                'max_variants' => $config->maxVariants,
                'target_audience' => $config->targetAudience,
                'content_pillar' => $config->contentPillar,
                'provider' => $config->provider,
                'model' => $config->model,
                'scorer_prompt_version' => $config->scorerPromptVersion,
                'generator_prompt_version' => $config->generatorPromptVersion,
                'experiment_id' => $config->experimentId,
                'memory_version' => $config->memoryVersion,
            ],
            idempotencyKey: $idempotencyKey,
        );

        RunHookAgentJob::dispatch($workspaceId, $run->id);

        Log::info('hook.agent.queued', [
            'workspace_id' => $workspaceId,
            'agent_run_id' => $run->id,
            'content_version_id' => $config->contentVersionId,
        ]);

        return $run;
    }
}
