<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Repositories;

use App\Domains\Experimentation\Contracts\ExperimentVariantRepositoryContract;
use App\Domains\Experimentation\Data\ExperimentVariantDto;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Models\ExperimentVariant;
use Illuminate\Support\Str;

final class ExperimentVariantRepository implements ExperimentVariantRepositoryContract
{
    /**
     * @return list<ExperimentVariantDto>
     */
    public function ensureTemplateVariants(string $workspaceId, string $experimentId, ExperimentType $type): array
    {
        $existing = $this->listByExperiment($workspaceId, $experimentId);
        if ($existing !== []) {
            return $existing;
        }

        $template = config('experimentation.experiment_templates.'.$type->value, []);
        $variants = $template['variants'] ?? [];

        foreach ($variants as $row) {
            ExperimentVariant::query()->create([
                'id' => (string) Str::uuid(),
                'workspace_id' => $workspaceId,
                'experiment_id' => $experimentId,
                'variant_key' => (string) ($row['key'] ?? 'variant'),
                'label' => $row['label'] ?? null,
                'is_control' => (bool) ($row['is_control'] ?? false),
                'traffic_weight' => (float) ($row['weight'] ?? 0.5),
                'payload' => $row['payload'] ?? [],
            ]);
        }

        return $this->listByExperiment($workspaceId, $experimentId);
    }

    /**
     * @return list<ExperimentVariantDto>
     */
    public function listByExperiment(string $workspaceId, string $experimentId): array
    {
        return ExperimentVariant::query()
            ->where('workspace_id', $workspaceId)
            ->where('experiment_id', $experimentId)
            ->orderBy('variant_key')
            ->get()
            ->map(fn ($m) => $this->toDto($m))
            ->all();
    }

    public function incrementAssignmentCount(string $workspaceId, string $variantId): void
    {
        ExperimentVariant::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $variantId)
            ->increment('assignment_count');
    }

    private function toDto(ExperimentVariant $model): ExperimentVariantDto
    {
        return new ExperimentVariantDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            experimentId: $model->experiment_id,
            variantKey: $model->variant_key,
            label: $model->label,
            isControl: (bool) $model->is_control,
            trafficWeight: (float) $model->traffic_weight,
            payload: $model->payload ?? [],
            metadata: $model->metadata ?? [],
            assignmentCount: (int) $model->assignment_count,
        );
    }
}
