<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookContentNotFoundException;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookValidationException;
use App\Domains\Agents\Agents\HookAgent\Services\HookAgentMemoryEnrichmentService;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\Agents\Contracts\AgentContract;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\Agents\Data\AgentResult;
use App\Domains\Content\Actions\PersistHookGeneratedOutputAction;
use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Events\HookScored;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;
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
        private readonly PersistHookGeneratedOutputAction $persistHookOutput,
        private readonly HookGeneratedOutputPersistenceService $generatedOutputPersistence,
        private readonly HookAgentMemoryEnrichmentService $memoryEnrichment,
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

        $reserved = $this->generatedOutputPersistence->ensureReserved($context, $config);
        $generatedOutputId = $reserved->id;

        Log::info('hook.agent.started', [
            'workspace_id' => $context->workspaceId,
            'agent_run_id' => $context->agentRunId,
            'content_version_id' => $config->contentVersionId,
            'generated_output_id' => $generatedOutputId,
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

        $brandMemory = $this->memoryEnrichment->enrich(
            $context->workspaceId,
            $hookText,
            $config,
        );

        Log::info('hook.agent.memory_enriched', [
            'workspace_id' => $context->workspaceId,
            'agent_run_id' => $context->agentRunId,
            'profile_id' => $brandMemory->profileId,
            'memory_version' => $brandMemory->memoryVersion,
            'used_fallback' => $brandMemory->usedFallback,
            'compact_chars' => strlen($brandMemory->compactBrandSection),
            'chunk_count' => count($brandMemory->selectedChunks),
        ]);

        try {
            $primary = $this->scoringService->score(
                workspaceId: $context->workspaceId,
                hookText: $hookText,
                config: $config,
                brandMemory: $brandMemory,
            );

            $variants = $this->generationService->generate(
                workspaceId: $context->workspaceId,
                hookText: $hookText,
                primaryScore: $primary,
                config: $config,
                brandMemory: $brandMemory,
            );

            $collection = new HookCollection(
                primary: $primary,
                variants: $variants,
                model: $config->resolvedModel(),
                experimentId: $config->experimentId,
            );

            $this->validator->validateCollection($collection, $config->maxVariants);

            $persisted = $this->persistHookOutput->execute(
                $context,
                $config,
                $collection,
                $brandMemory->memoryContextForPersistence(),
                $generatedOutputId,
                $brandMemory,
            );

            $hookScore = $persisted['hook_score'];
            $generatedOutputId = $persisted['generated_output_id'];

            event(new HookScored(
                workspaceId: $context->workspaceId,
                contentVersionId: $config->contentVersionId,
                agentRunId: $context->agentRunId,
                hookScoreId: $hookScore->id,
                payload: array_merge(
                    $collection->toAnalyticsPayload($config->contentVersionId, $context->agentRunId),
                    $brandMemory->toAnalyticsPayload(),
                    [
                        'workflow_run_id' => $context->option('workflow_run_id'),
                        'hook_score_id' => $hookScore->id,
                        'generated_output_id' => $generatedOutputId,
                    ],
                ),
                generatedOutputId: $generatedOutputId,
            ));

            Log::info('hook.agent.completed', [
                'workspace_id' => $context->workspaceId,
                'agent_run_id' => $context->agentRunId,
                'hook_score_id' => $hookScore->id,
                'generated_output_id' => $generatedOutputId,
                'overall' => $collection->primary->overall,
            ]);

            return new AgentResult(
                output: array_merge($collection->toAgentOutput(), [
                    'hook_score_id' => $hookScore->id,
                    'generated_output_id' => $generatedOutputId,
                    'brand_memory' => $brandMemory->toAnalyticsPayload(),
                ]),
                summary: sprintf('Hook scored %.1f with %d variants.', $collection->primary->overall, count($variants)),
            );
        } catch (Throwable $e) {
            Log::error('hook.agent.failed', [
                'workspace_id' => $context->workspaceId,
                'agent_run_id' => $context->agentRunId,
                'generated_output_id' => $generatedOutputId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
