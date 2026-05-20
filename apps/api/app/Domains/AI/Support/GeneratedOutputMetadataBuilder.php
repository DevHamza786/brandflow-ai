<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

use App\Domains\AI\Data\GeneratedOutputMetadataDto;

/**
 * Fluent builder for structured metadata (orchestration, RAG audit, fine-tuning flags).
 */
final class GeneratedOutputMetadataBuilder
{
    /** @var array<string, mixed> */
    private array $data = [];

    public static function make(): self
    {
        return new self;
    }

    public function traceId(?string $traceId): self
    {
        if ($traceId !== null) {
            $this->data['trace_id'] = $traceId;
        }

        return $this;
    }

    /**
     * @param  list<string>  $chunkIds
     */
    public function memoryChunkIds(array $chunkIds): self
    {
        if ($chunkIds !== []) {
            $this->data['memory_chunk_ids'] = array_values($chunkIds);
        }

        return $this;
    }

    public function embeddingId(?string $embeddingId): self
    {
        if ($embeddingId !== null) {
            $this->data['embedding_id'] = $embeddingId;
        }

        return $this;
    }

    public function memoryVersion(?int $version): self
    {
        if ($version !== null) {
            $this->data['memory_version'] = $version;
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $usage
     */
    public function tokenUsage(array $usage): self
    {
        if ($usage !== []) {
            $this->data['token_usage'] = $usage;
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $orchestration
     */
    public function orchestration(
        ?string $workflowRunId = null,
        ?string $agentSlug = null,
        ?string $stepId = null,
        array $orchestration = [],
    ): self {
        $merged = array_filter(array_merge(
            [
                'workflow_run_id' => $workflowRunId,
                'agent_slug' => $agentSlug,
                'step_id' => $stepId,
            ],
            $orchestration,
        ), static fn ($v) => $v !== null && $v !== '');

        if ($merged !== []) {
            $this->data['orchestration'] = $merged;
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $fineTuning
     */
    public function fineTuning(
        bool $feedbackEligible = false,
        ?string $datasetVersion = null,
        array $fineTuning = [],
    ): self {
        $merged = array_filter(array_merge(
            [
                'feedback_eligible' => $feedbackEligible,
                'dataset_version' => $datasetVersion,
            ],
            $fineTuning,
        ), static fn ($v) => $v !== null && $v !== false && $v !== '');

        if ($merged !== []) {
            $this->data['fine_tuning'] = $merged;
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $extras
     */
    public function extras(array $extras): self
    {
        $this->data = array_merge($this->data, $extras);

        return $this;
    }

    public function build(): GeneratedOutputMetadataDto
    {
        return GeneratedOutputMetadataDto::fromArray($this->data);
    }
}
