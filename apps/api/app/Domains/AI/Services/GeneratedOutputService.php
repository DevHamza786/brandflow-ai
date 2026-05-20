<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\GeneratedOutputPersistenceContract;
use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputMetadataDto;
use App\Domains\AI\Data\GeneratedOutputPayloadDto;
use App\Domains\AI\Data\GeneratedOutputScoresDto;
use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Events\GeneratedOutputCompleted;
use App\Domains\AI\Events\GeneratedOutputFailed;
use App\Domains\AI\Events\GeneratedOutputPersisted;
use Illuminate\Support\Facades\Event;

final class GeneratedOutputService implements GeneratedOutputPersistenceContract
{
    public function __construct(
        private readonly GeneratedOutputRepositoryContract $repository,
    ) {
    }

    public function begin(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        $pending = new CreateGeneratedOutputDto(
            workspaceId: $dto->workspaceId,
            type: $dto->type,
            input: $dto->input,
            workflowRunId: $dto->workflowRunId,
            agentRunId: $dto->agentRunId,
            contentVersionId: $dto->contentVersionId,
            provider: $dto->provider,
            model: $dto->model,
            promptVersion: $dto->promptVersion,
            output: null,
            scores: $dto->scores,
            metadata: $dto->metadata,
            status: GeneratedOutputStatus::Pending,
        );

        $record = $this->repository->upsertForExecution($pending);

        Event::dispatch(new GeneratedOutputPersisted(
            workspaceId: $record->workspaceId,
            generatedOutputId: $record->id,
            type: $record->type,
            status: $record->status,
            agentRunId: $record->agentRunId,
            workflowRunId: $record->workflowRunId,
        ));

        return $record;
    }

    public function markProcessing(string $workspaceId, string $generatedOutputId): GeneratedOutputDto
    {
        return $this->repository->updateStatus(
            $workspaceId,
            $generatedOutputId,
            GeneratedOutputStatus::Processing,
        );
    }

    public function complete(
        string $workspaceId,
        string $generatedOutputId,
        GeneratedOutputPayloadDto $output,
        ?GeneratedOutputScoresDto $scores = null,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto {
        $record = $this->repository->updateStatus(
            $workspaceId,
            $generatedOutputId,
            GeneratedOutputStatus::Completed,
            output: $output->payload,
            scores: $scores?->toArray(),
            metadataPatch: $metadataPatch?->toStorageArray(),
        );

        Event::dispatch(new GeneratedOutputCompleted(
            workspaceId: $record->workspaceId,
            generatedOutputId: $record->id,
            type: $record->type,
            agentRunId: $record->agentRunId,
            workflowRunId: $record->workflowRunId,
            contentVersionId: $record->contentVersionId,
            payload: [
                'provider' => $record->provider,
                'model' => $record->model,
                'scores_overall' => $record->scores->overall,
            ],
        ));

        return $record;
    }

    public function fail(
        string $workspaceId,
        string $generatedOutputId,
        array $errorPayload,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto {
        $record = $this->repository->updateStatus(
            $workspaceId,
            $generatedOutputId,
            GeneratedOutputStatus::Failed,
            output: ['error' => $errorPayload],
            metadataPatch: $metadataPatch?->toStorageArray(),
        );

        Event::dispatch(new GeneratedOutputFailed(
            workspaceId: $record->workspaceId,
            generatedOutputId: $record->id,
            type: $record->type,
            agentRunId: $record->agentRunId,
            workflowRunId: $record->workflowRunId,
            error: $errorPayload,
        ));

        return $record;
    }

    public function record(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        $completed = new CreateGeneratedOutputDto(
            workspaceId: $dto->workspaceId,
            type: $dto->type,
            input: $dto->input,
            workflowRunId: $dto->workflowRunId,
            agentRunId: $dto->agentRunId,
            contentVersionId: $dto->contentVersionId,
            provider: $dto->provider,
            model: $dto->model,
            promptVersion: $dto->promptVersion,
            output: $dto->output,
            scores: $dto->scores,
            metadata: $dto->metadata,
            status: GeneratedOutputStatus::Completed,
        );

        $record = $this->repository->upsertForExecution($completed);

        Event::dispatch(new GeneratedOutputCompleted(
            workspaceId: $record->workspaceId,
            generatedOutputId: $record->id,
            type: $record->type,
            agentRunId: $record->agentRunId,
            workflowRunId: $record->workflowRunId,
            contentVersionId: $record->contentVersionId,
            payload: ['provider' => $record->provider, 'model' => $record->model],
        ));

        return $record;
    }

    public function resolveForAgentRun(
        string $workspaceId,
        string $agentRunId,
    ): ?GeneratedOutputDto {
        return $this->repository->findLatestByAgentRun($workspaceId, $agentRunId);
    }

    public function linkWorkflowRun(
        string $workspaceId,
        string $generatedOutputId,
        string $workflowRunId,
    ): GeneratedOutputDto {
        return $this->repository->linkWorkflowRun($workspaceId, $generatedOutputId, $workflowRunId);
    }
}
