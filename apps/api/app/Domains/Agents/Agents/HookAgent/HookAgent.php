<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookContentNotFoundException;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookValidationException;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\Agents\Contracts\AgentContract;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\Agents\Data\AgentResult;
use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;
use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Events\HookScored;
use App\Domains\Content\Services\HookGenerationService;
use App\Domains\Content\Services\HookScoringService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Hook Lab agent — score opening lines and generate variants.
 *
 * @see docs/AGENTS.md §4.1 HookAgent
 */
final class HookAgent implements AgentContract
{
    public function __construct(
        private readonly ContentVersionRepositoryContract $contentVersions,
        private readonly HookScoringService $scoringService,
        private readonly HookGenerationService $generationService,
        private readonly HookScoreRepositoryContract $hookScores,
        private readonly MemoryRetrievalServiceContract $memoryRetrieval,
        private readonly HookResponseValidator $validator,
    ) {
    }

    public function slug(): string
    {
        return 'hook';
    }

    public function run(AgentContext $context): AgentResult
    {
        $config = HookAgentConfig::fromAgentContext($context);

        if ($config->contentVersionId === '') {
            throw new HookValidationException('content_version_id is required.');
        }

        Log::info('hook.agent.started', [
            'workspace_id' => $context->workspaceId,
            'agent_run_id' => $context->agentRunId,
            'content_version_id' => $config->contentVersionId,
        ]);

        $version = $this->contentVersions->findForWorkspace(
            $context->workspaceId,
            $config->contentVersionId,
        );

        if ($version === null) {
            throw new HookContentNotFoundException(
                "Content version [{$config->contentVersionId}] not found.",
                ['workspace_id' => $context->workspaceId],
            );
        }

        $hookText = $version->extractOpeningLines();
        $this->validator->validateHookText($hookText);

        $memory = $this->memoryRetrieval->retrieve(
            workspaceId: $context->workspaceId,
            query: $hookText,
            types: ['voice', 'anti_patterns', 'performance'],
            memoryVersion: $config->memoryVersion,
            limit: (int) config('ai.memory.max_chunks', 10),
        );

        try {
            $primary = $this->scoringService->score(
                workspaceId: $context->workspaceId,
                hookText: $hookText,
                config: $config,
                memory: $memory,
            );

            $variants = $this->generationService->generate(
                workspaceId: $context->workspaceId,
                hookText: $hookText,
                primaryScore: $primary,
                config: $config,
                memory: $memory,
            );

            $collection = new HookCollection(
                primary: $primary,
                variants: $variants,
                model: $config->resolvedModel(),
                experimentId: $config->experimentId,
            );

            $this->validator->validateCollection($collection, $config->maxVariants);

            $hookScore = $this->hookScores->persistFromCollection(
                workspaceId: $context->workspaceId,
                contentVersionId: $config->contentVersionId,
                agentRunId: $context->agentRunId,
                collection: $collection,
                model: $config->resolvedModel(),
                promptVersion: $config->scorerPromptVersion,
                traceId: null,
                metadata: [
                    'experiment_id' => $config->experimentId,
                    'generator_prompt_version' => $config->generatorPromptVersion,
                ],
            );

            event(new HookScored(
                workspaceId: $context->workspaceId,
                contentVersionId: $config->contentVersionId,
                agentRunId: $context->agentRunId,
                hookScoreId: $hookScore->id,
                payload: $collection->toAnalyticsPayload($config->contentVersionId, $context->agentRunId),
            ));

            Log::info('hook.agent.completed', [
                'workspace_id' => $context->workspaceId,
                'agent_run_id' => $context->agentRunId,
                'hook_score_id' => $hookScore->id,
                'overall' => $collection->primary->overall,
            ]);

            return new AgentResult(
                output: array_merge($collection->toAgentOutput(), [
                    'hook_score_id' => $hookScore->id,
                ]),
                summary: sprintf('Hook scored %.1f with %d variants.', $collection->primary->overall, count($variants)),
            );
        } catch (Throwable $e) {
            Log::error('hook.agent.failed', [
                'workspace_id' => $context->workspaceId,
                'agent_run_id' => $context->agentRunId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
