<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputMetadataDto;
use App\Domains\AI\Data\GeneratedOutputPayloadDto;
use App\Domains\AI\Data\GeneratedOutputScoresDto;
use App\Domains\AI\Enums\GeneratedOutputStatus;

/**
 * Workflow / agent integration surface — orchestrators depend on this, not repositories.
 */
interface GeneratedOutputPersistenceContract
{
    /**
     * Reserve a row before async agent execution (status: pending).
     */
    public function begin(CreateGeneratedOutputDto $dto): GeneratedOutputDto;

    /**
     * Mark in-flight (status: processing) — safe to call from queue workers.
     */
    public function markProcessing(string $workspaceId, string $generatedOutputId): GeneratedOutputDto;

    /**
     * Persist final agent result and emit domain event.
     */
    public function complete(
        string $workspaceId,
        string $generatedOutputId,
        GeneratedOutputPayloadDto $output,
        ?GeneratedOutputScoresDto $scores = null,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto;

    public function fail(
        string $workspaceId,
        string $generatedOutputId,
        array $errorPayload,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto;

    /**
     * One-shot persist when output is already available (sync or replay).
     */
    public function record(CreateGeneratedOutputDto $dto): GeneratedOutputDto;

    public function resolveForAgentRun(
        string $workspaceId,
        string $agentRunId,
    ): ?GeneratedOutputDto;

    public function linkWorkflowRun(
        string $workspaceId,
        string $generatedOutputId,
        string $workflowRunId,
    ): GeneratedOutputDto;
}
