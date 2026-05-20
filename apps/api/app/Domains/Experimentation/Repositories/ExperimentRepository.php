<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Repositories;

use App\Domains\Experimentation\Contracts\ExperimentRepositoryContract;
use App\Domains\Experimentation\Data\ExperimentDto;
use App\Domains\Experimentation\Enums\ExperimentStatus;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Models\Experiment;
use Illuminate\Support\Str;

final class ExperimentRepository implements ExperimentRepositoryContract
{
    public function findOrCreateByType(string $workspaceId, ExperimentType $type): ExperimentDto
    {
        $slug = 'exp:'.$type->value;

        $existing = Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('slug', $slug)
            ->whereIn('status', [
                ExperimentStatus::Draft->value,
                ExperimentStatus::Running->value,
                ExperimentStatus::Paused->value,
            ])
            ->first();

        if ($existing !== null) {
            return $this->toDto($existing);
        }

        $template = config('experimentation.experiment_templates.'.$type->value, []);
        $name = (string) ($template['name'] ?? ucfirst(str_replace('_', ' ', $type->value)));

        $model = Experiment::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'slug' => $slug,
            'name' => $name,
            'experiment_type' => $type->value,
            'status' => ExperimentStatus::Running->value,
            'hypothesis' => null,
            'config' => [
                'publish_variants' => false,
                'isolated_analytics' => true,
            ],
            'started_at' => now(),
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?ExperimentDto
    {
        $model = Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function findBySlug(string $workspaceId, string $slug): ?ExperimentDto
    {
        $model = Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('slug', $slug)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function markRunning(string $workspaceId, string $experimentId): ExperimentDto
    {
        $model = Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $experimentId)
            ->firstOrFail();

        $model->update([
            'status' => ExperimentStatus::Running->value,
            'started_at' => $model->started_at ?? now(),
        ]);

        return $this->toDto($model->fresh());
    }

    /**
     * @return list<ExperimentDto>
     */
    public function listRunning(string $workspaceId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));

        return Experiment::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', ExperimentStatus::Running->value)
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->toDto($m))
            ->all();
    }

    private function toDto(Experiment $model): ExperimentDto
    {
        return new ExperimentDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            slug: $model->slug,
            name: $model->name,
            experimentType: ExperimentType::from($model->experiment_type),
            status: ExperimentStatus::from($model->status),
            hypothesis: $model->hypothesis,
            config: $model->config ?? [],
            mlState: $model->ml_state ?? [],
            metadata: $model->metadata ?? [],
            optimizationLoopId: $model->optimization_loop_id,
            workflowBlueprintId: $model->workflow_blueprint_id,
            agentCoordinationId: $model->agent_coordination_id,
            startedAt: $model->started_at,
            endedAt: $model->ended_at,
        );
    }
}
