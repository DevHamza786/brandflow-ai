<?php

declare(strict_types=1);

namespace App\Domains\AI\Repositories;

use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\AI\Models\GeneratedOutput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class GeneratedOutputRepository implements GeneratedOutputRepositoryContract
{
    public function create(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        $model = GeneratedOutput::query()->create($this->attributesFromDto($dto));

        return GeneratedOutputDto::fromModel($model);
    }

    public function upsertForAgentRun(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        return $this->upsertForExecution($dto);
    }

    public function upsertForExecution(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        $existing = $this->findExistingForUpsert($dto);

        if ($existing === null) {
            return $this->create($dto);
        }

        $existing->fill($this->attributesFromDto($dto, mergeMetadata: $existing->metadata ?? []));
        $existing->save();

        return GeneratedOutputDto::fromModel($existing->fresh());
    }

    public function findByWorkflowRun(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputType $type,
    ): ?GeneratedOutputDto {
        $model = $this->scopedQuery($workspaceId)
            ->where('workflow_run_id', $workflowRunId)
            ->where('type', $type->value)
            ->first();

        return $model ? GeneratedOutputDto::fromModel($model) : null;
    }

    private function findExistingForUpsert(CreateGeneratedOutputDto $dto): ?GeneratedOutput
    {
        if ($dto->workflowRunId !== null) {
            return GeneratedOutput::query()
                ->where('workspace_id', $dto->workspaceId)
                ->where('workflow_run_id', $dto->workflowRunId)
                ->where('type', $dto->type->value)
                ->first();
        }

        if ($dto->agentRunId !== null) {
            return GeneratedOutput::query()
                ->where('workspace_id', $dto->workspaceId)
                ->where('agent_run_id', $dto->agentRunId)
                ->where('type', $dto->type->value)
                ->first();
        }

        return null;
    }

    public function findById(string $workspaceId, string $id): ?GeneratedOutputDto
    {
        $model = $this->scopedQuery($workspaceId)
            ->whereKey($id)
            ->first();

        return $model ? GeneratedOutputDto::fromModel($model) : null;
    }

    public function findLatestByAgentRun(
        string $workspaceId,
        string $agentRunId,
        ?GeneratedOutputType $type = null,
    ): ?GeneratedOutputDto {
        $query = $this->scopedQuery($workspaceId)
            ->where('agent_run_id', $agentRunId)
            ->latest('created_at');

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        $model = $query->first();

        return $model ? GeneratedOutputDto::fromModel($model) : null;
    }

    public function listByWorkflowRun(string $workspaceId, string $workflowRunId): array
    {
        return $this->scopedQuery($workspaceId)
            ->where('workflow_run_id', $workflowRunId)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (GeneratedOutput $m) => GeneratedOutputDto::fromModel($m))
            ->all();
    }

    public function listByContentVersion(
        string $workspaceId,
        string $contentVersionId,
        ?GeneratedOutputType $type = null,
        int $limit = 20,
    ): array {
        $query = $this->scopedQuery($workspaceId)
            ->where('content_version_id', $contentVersionId)
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        return $query
            ->get()
            ->map(static fn (GeneratedOutput $m) => GeneratedOutputDto::fromModel($m))
            ->all();
    }

    public function paginateForWorkspace(
        string $workspaceId,
        array $filters = [],
        int $perPage = 25,
    ): LengthAwarePaginator {
        $query = $this->scopedQuery($workspaceId)->orderByDesc('created_at');

        if (isset($filters['type']) && $filters['type'] instanceof GeneratedOutputType) {
            $query->where('type', $filters['type']->value);
        }

        if (isset($filters['status']) && $filters['status'] instanceof GeneratedOutputStatus) {
            $query->where('status', $filters['status']->value);
        }

        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query
            ->paginate($perPage)
            ->through(static fn (GeneratedOutput $m) => GeneratedOutputDto::fromModel($m));
    }

    public function updateStatus(
        string $workspaceId,
        string $id,
        GeneratedOutputStatus $status,
        ?array $output = null,
        ?array $scores = null,
        ?array $metadataPatch = null,
    ): GeneratedOutputDto {
        $model = $this->scopedQuery($workspaceId)->whereKey($id)->firstOrFail();

        $model->status = $status->value;

        if ($output !== null) {
            $model->output = $output;
        }

        if ($scores !== null) {
            $model->scores = $scores;
        }

        if ($metadataPatch !== null) {
            $model->metadata = array_merge($model->metadata ?? [], $metadataPatch);
        }

        $model->save();

        return GeneratedOutputDto::fromModel($model->fresh());
    }

    public function markSuperseded(string $workspaceId, string $id): GeneratedOutputDto
    {
        return $this->updateStatus($workspaceId, $id, GeneratedOutputStatus::Superseded);
    }

    public function linkWorkflowRun(
        string $workspaceId,
        string $id,
        string $workflowRunId,
    ): GeneratedOutputDto {
        $model = $this->scopedQuery($workspaceId)->whereKey($id)->firstOrFail();
        $model->workflow_run_id = $workflowRunId;
        $model->save();

        return GeneratedOutputDto::fromModel($model->fresh());
    }

    /**
     * @return Builder<GeneratedOutput>
     */
    private function scopedQuery(string $workspaceId): Builder
    {
        return GeneratedOutput::query()->where('workspace_id', $workspaceId);
    }

    /**
     * @param  array<string, mixed>|null  $mergeMetadata
     * @return array<string, mixed>
     */
    private function attributesFromDto(CreateGeneratedOutputDto $dto, ?array $mergeMetadata = null): array
    {
        $metadata = $dto->metadata?->toStorageArray() ?? [];

        if ($mergeMetadata !== null) {
            $metadata = array_merge($mergeMetadata, $metadata);
        }

        return [
            'workspace_id' => $dto->workspaceId,
            'workflow_run_id' => $dto->workflowRunId,
            'agent_run_id' => $dto->agentRunId,
            'content_version_id' => $dto->contentVersionId,
            'type' => $dto->type->value,
            'provider' => $dto->provider,
            'model' => $dto->model,
            'prompt_version' => $dto->promptVersion,
            'input' => $dto->input->payload,
            'output' => $dto->output?->payload,
            'scores' => $dto->scores?->toArray() ?? [],
            'metadata' => $metadata,
            'status' => $dto->status->value,
        ];
    }
}
