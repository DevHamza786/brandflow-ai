<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\AI\Contracts\GeneratedOutputPersistenceContract;
use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\AI\Contracts\WorkflowGeneratedOutputContract;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\Brand\Data\BrandMemoryContext;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Models\HookScore;
use App\Domains\Content\Support\HookGeneratedOutputMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Transaction-safe Hook Lab persistence into hook_scores + generated_outputs.
 */
final class HookGeneratedOutputPersistenceService
{
    public function __construct(
        private readonly GeneratedOutputPersistenceContract $generatedOutputs,
        private readonly GeneratedOutputRepositoryContract $generatedOutputRepository,
        private readonly HookScoreRepositoryContract $hookScores,
        private readonly HookGeneratedOutputMapper $mapper,
        private readonly WorkflowGeneratedOutputContract $workflowBridge,
    ) {
    }

    /**
     * Reserve one generated_outputs row per workflow execution (retry-safe upsert).
     */
    public function reserveForWorkflow(
        AgentContext $context,
        HookAgentConfig $config,
        string $workflowRunId,
    ): GeneratedOutputDto {
        $dto = $this->mapper->toReserveDto($context, $config, $workflowRunId);

        $record = $this->generatedOutputs->begin($dto);

        Log::info('hook.generated_output.reserved', [
            'workspace_id' => $context->workspaceId,
            'workflow_run_id' => $workflowRunId,
            'agent_run_id' => $context->agentRunId,
            'generated_output_id' => $record->id,
        ]);

        return $record;
    }

    public function markProcessing(string $workspaceId, string $generatedOutputId): GeneratedOutputDto
    {
        $record = $this->generatedOutputs->markProcessing($workspaceId, $generatedOutputId);

        Log::info('hook.generated_output.processing', [
            'workspace_id' => $workspaceId,
            'generated_output_id' => $generatedOutputId,
        ]);

        return $record;
    }

    /**
     * @return array{hook_score: HookScore, generated_output: GeneratedOutputDto}
     */
    public function persistHookResults(
        AgentContext $context,
        HookAgentConfig $config,
        HookCollection $collection,
        MemoryContext $memory,
        string $generatedOutputId,
        ?string $workflowRunId = null,
        ?BrandMemoryContext $brandMemory = null,
    ): array {
        $workflowRunId ??= $context->option('workflow_run_id');

        try {
            return DB::transaction(function () use (
                $context,
                $config,
                $collection,
                $memory,
                $generatedOutputId,
                $workflowRunId,
                $brandMemory,
            ): array {
                $hookScore = $this->hookScores->persistFromCollection(
                    workspaceId: $context->workspaceId,
                    contentVersionId: $config->contentVersionId,
                    agentRunId: $context->agentRunId,
                    collection: $collection,
                    model: $config->resolvedModel(),
                    promptVersion: $config->scorerPromptVersion,
                    traceId: $collection->traceId,
                    metadata: [
                        'experiment_id' => $config->experimentId,
                        'generator_prompt_version' => $config->generatorPromptVersion,
                        'generated_output_id' => $generatedOutputId,
                    ],
                );

                $metadata = $this->mapper->buildMetadata(
                    $context,
                    $config,
                    is_string($workflowRunId) ? $workflowRunId : null,
                    $memory,
                    $hookScore->id,
                    $brandMemory,
                );

                $generatedOutput = $this->generatedOutputs->complete(
                    workspaceId: $context->workspaceId,
                    generatedOutputId: $generatedOutputId,
                    output: $this->mapper->toCompletedPayload($collection, $hookScore->id),
                    scores: $this->mapper->toScoresDto($collection),
                    metadataPatch: $metadata,
                );

                if (is_string($workflowRunId) && $workflowRunId !== '') {
                    $this->workflowBridge->attachToWorkflowContext(
                        $context->workspaceId,
                        $workflowRunId,
                        $generatedOutput,
                    );
                }

                Log::info('hook.generated_output.completed', [
                    'workspace_id' => $context->workspaceId,
                    'workflow_run_id' => $workflowRunId,
                    'agent_run_id' => $context->agentRunId,
                    'generated_output_id' => $generatedOutput->id,
                    'hook_score_id' => $hookScore->id,
                    'overall' => $collection->primary->overall,
                    'variant_count' => count($collection->variants),
                ]);

                return [
                    'hook_score' => $hookScore,
                    'generated_output' => $generatedOutput,
                ];
            });
        } catch (Throwable $e) {
            Log::error('hook.generated_output.persist_failed', [
                'workspace_id' => $context->workspaceId,
                'generated_output_id' => $generatedOutputId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public function markFailed(
        string $workspaceId,
        string $generatedOutputId,
        array $error,
        ?string $workflowRunId = null,
    ): GeneratedOutputDto {
        $record = $this->generatedOutputs->fail(
            $workspaceId,
            $generatedOutputId,
            $error,
        );

        Log::error('hook.generated_output.failed', [
            'workspace_id' => $workspaceId,
            'generated_output_id' => $generatedOutputId,
            'workflow_run_id' => $workflowRunId,
            'error' => $error,
        ]);

        return $record;
    }

    /**
     * Ensures a generated_outputs row exists (workflow or standalone agent run).
     */
    public function ensureReserved(
        AgentContext $context,
        HookAgentConfig $config,
    ): GeneratedOutputDto {
        $existingId = $this->resolveGeneratedOutputId($context);

        if ($existingId !== null) {
            $found = $this->generatedOutputRepository->findById($context->workspaceId, $existingId);

            if ($found !== null) {
                return $found;
            }
        }

        $workflowRunId = $context->option('workflow_run_id');

        if (is_string($workflowRunId) && $workflowRunId !== '') {
            return $this->reserveForWorkflow($context, $config, $workflowRunId);
        }

        $record = $this->generatedOutputs->begin(
            $this->mapper->toReserveDto($context, $config, null),
        );

        Log::info('hook.generated_output.reserved_standalone', [
            'workspace_id' => $context->workspaceId,
            'agent_run_id' => $context->agentRunId,
            'generated_output_id' => $record->id,
        ]);

        return $record;
    }

    public function resolveGeneratedOutputId(AgentContext $context): ?string
    {
        $fromOptions = $context->option('generated_output_id');

        if (is_string($fromOptions) && $fromOptions !== '') {
            return $fromOptions;
        }

        $workflowRunId = $context->option('workflow_run_id');

        if (! is_string($workflowRunId) || $workflowRunId === '') {
            $found = $this->generatedOutputRepository->findLatestByAgentRun(
                $context->workspaceId,
                $context->agentRunId,
                GeneratedOutputType::Hook,
            );

            return $found?->id;
        }

        $found = $this->generatedOutputRepository->findByWorkflowRun(
            $context->workspaceId,
            $workflowRunId,
            GeneratedOutputType::Hook,
        );

        return $found?->id;
    }
}
