<?php

declare(strict_types=1);

namespace App\Domains\Content\Support;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputInputDto;
use App\Domains\AI\Data\GeneratedOutputMetadataDto;
use App\Domains\AI\Data\GeneratedOutputPayloadDto;
use App\Domains\AI\Data\GeneratedOutputScoresDto;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\Brand\Data\BrandMemoryContext;
use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\AI\Support\GeneratedOutputMetadataBuilder;

/**
 * Maps Hook Lab artifacts to GeneratedOutput DTOs (analytics + replay).
 */
final class HookGeneratedOutputMapper
{
    public function toReserveDto(
        AgentContext $context,
        HookAgentConfig $config,
        ?string $workflowRunId,
        ?string $generatedOutputId = null,
    ): CreateGeneratedOutputDto {
        return new CreateGeneratedOutputDto(
            workspaceId: $context->workspaceId,
            type: GeneratedOutputType::Hook,
            input: GeneratedOutputInputDto::fromArray([
                'content_version_id' => $config->contentVersionId,
                'max_variants' => $config->maxVariants,
                'target_audience' => $config->targetAudience,
                'content_pillar' => $config->contentPillar,
                'agent_run_id' => $context->agentRunId,
                'workflow_run_id' => $workflowRunId,
                'generated_output_id' => $generatedOutputId,
            ]),
            workflowRunId: $workflowRunId,
            agentRunId: $context->agentRunId,
            contentVersionId: $config->contentVersionId,
            provider: $config->resolvedProvider(),
            model: $config->resolvedModel(),
            promptVersion: $config->scorerPromptVersion,
            metadata: $this->buildMetadata($context, $config, $workflowRunId, memory: null),
            status: GeneratedOutputStatus::Pending,
        );
    }

    public function toCompletedPayload(
        HookCollection $collection,
        string $hookScoreId,
    ): GeneratedOutputPayloadDto {
        return GeneratedOutputPayloadDto::fromArray([
            'hook_score_id' => $hookScoreId,
            'primary' => [
                'hook_text' => $collection->primary->hookText,
                'overall' => $collection->primary->overall,
                'dimensions' => $collection->primary->dimensions->toArray(),
                'suggestions' => $collection->primary->suggestions,
            ],
            'variants' => array_map(
                static fn (HookVariant $variant) => $variant->toArray(),
                $collection->variants,
            ),
            'variant_count' => count($collection->variants),
            'trace_id' => $collection->traceId,
            'model' => $collection->model,
            'experiment_id' => $collection->experimentId,
        ]);
    }

    public function toScoresDto(HookCollection $collection): GeneratedOutputScoresDto
    {
        return GeneratedOutputScoresDto::fromArray([
            'overall' => $collection->primary->overall,
            'dimensions' => $collection->primary->dimensions->toArray(),
            'variant_scores' => array_map(
                static fn (HookVariant $variant) => [
                    'overall' => $variant->overall,
                    'dimensions' => $variant->dimensions->toArray(),
                ],
                $collection->variants,
            ),
            'top_variant_score' => $collection->variants[0]->overall ?? null,
        ]);
    }

    public function buildMetadata(
        AgentContext $context,
        HookAgentConfig $config,
        ?string $workflowRunId,
        ?MemoryContext $memory,
        ?string $hookScoreId = null,
        ?BrandMemoryContext $brandMemory = null,
    ): GeneratedOutputMetadataDto {
        $chunkIds = [];

        if ($memory !== null) {
            foreach ($memory->chunks as $chunk) {
                $chunkIds[] = $chunk->id;
            }
        }

        $personalization = $brandMemory?->toAnalyticsPayload() ?? [];

        return GeneratedOutputMetadataBuilder::make()
            ->traceId($context->option('trace_id'))
            ->memoryChunkIds($chunkIds)
            ->memoryVersion($config->memoryVersion ?? $brandMemory?->memoryVersion ?? $memory?->memoryVersion)
            ->orchestration(
                workflowRunId: $workflowRunId,
                agentSlug: 'hook',
                stepId: 'persist_results',
                orchestration: [
                    'content_version_id' => $config->contentVersionId,
                    'hook_score_id' => $hookScoreId,
                ],
            )
            ->fineTuning(
                feedbackEligible: true,
                datasetVersion: 'hook_lab_v1',
                fineTuning: [
                    'scorer_prompt_version' => $config->scorerPromptVersion,
                    'generator_prompt_version' => $config->generatorPromptVersion,
                ],
            )
            ->extras([
                'provider' => $config->resolvedProvider(),
                'model' => $config->resolvedModel(),
                'experiment_id' => $config->experimentId,
                'workflow_slug' => 'hook_generation',
                'brand_profile_id' => $brandMemory?->profileId,
                'personalization' => $personalization,
                'replay' => [
                    'agent_run_id' => $context->agentRunId,
                    'workflow_run_id' => $workflowRunId,
                ],
            ])
            ->build();
    }
}
