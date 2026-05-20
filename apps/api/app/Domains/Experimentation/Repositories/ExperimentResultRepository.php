<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Repositories;

use App\Domains\Experimentation\Contracts\ExperimentResultRepositoryContract;
use App\Domains\Experimentation\Data\CreateExperimentResultDto;
use App\Domains\Experimentation\Data\ExperimentResultDto;
use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Experimentation\Models\ExperimentResult;
use Illuminate\Support\Str;

final class ExperimentResultRepository implements ExperimentResultRepositoryContract
{
    public function create(CreateExperimentResultDto $dto): ExperimentResultDto
    {
        if ($dto->idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($dto->workspaceId, $dto->idempotencyKey);
            if ($existing !== null) {
                return $existing;
            }
        }

        $model = ExperimentResult::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'experiment_id' => $dto->experimentId,
            'experiment_variant_id' => $dto->experimentVariantId,
            'result_type' => $dto->resultType->value,
            'entity_type' => $dto->entityType,
            'entity_id' => $dto->entityId,
            'subject_key' => $dto->subjectKey,
            'metrics' => $dto->metrics,
            'statistical_summary' => $dto->statisticalSummary,
            'idempotency_key' => $dto->idempotencyKey,
            'trace_id' => $dto->traceId,
        ]);

        return $this->toDto($model);
    }

    public function findAssignment(
        string $workspaceId,
        string $experimentId,
        string $subjectKey,
    ): ?ExperimentResultDto {
        $model = ExperimentResult::query()
            ->where('workspace_id', $workspaceId)
            ->where('experiment_id', $experimentId)
            ->where('subject_key', $subjectKey)
            ->where('result_type', ExperimentResultType::Assignment->value)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    /**
     * @return list<ExperimentResultDto>
     */
    public function listObservationsByVariant(string $workspaceId, string $variantId): array
    {
        return ExperimentResult::query()
            ->where('workspace_id', $workspaceId)
            ->where('experiment_variant_id', $variantId)
            ->where('result_type', ExperimentResultType::Observation->value)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($m) => $this->toDto($m))
            ->all();
    }

    public function findByIdempotencyKey(string $workspaceId, string $key): ?ExperimentResultDto
    {
        $model = ExperimentResult::query()
            ->where('workspace_id', $workspaceId)
            ->where('idempotency_key', $key)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    private function toDto(ExperimentResult $model): ExperimentResultDto
    {
        return new ExperimentResultDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            experimentId: $model->experiment_id,
            experimentVariantId: $model->experiment_variant_id,
            resultType: ExperimentResultType::from($model->result_type),
            entityType: $model->entity_type,
            entityId: $model->entity_id,
            subjectKey: $model->subject_key,
            metrics: $model->metrics ?? [],
            statisticalSummary: $model->statistical_summary ?? [],
            traceId: $model->trace_id,
            createdAt: $model->created_at,
        );
    }
}
