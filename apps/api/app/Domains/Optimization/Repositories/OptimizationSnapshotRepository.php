<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Repositories;

use App\Domains\Optimization\Contracts\OptimizationSnapshotRepositoryContract;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationSnapshotDto;
use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;
use App\Domains\Optimization\Models\OptimizationSnapshot;
use Illuminate\Support\Str;

final class OptimizationSnapshotRepository implements OptimizationSnapshotRepositoryContract
{
    public function create(CreateOptimizationSnapshotDto $dto): OptimizationSnapshotDto
    {
        $model = OptimizationSnapshot::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'optimization_loop_id' => $dto->optimizationLoopId,
            'cycle_number' => $dto->cycleNumber,
            'status' => $dto->status->value,
            'engine' => $dto->engine,
            'focus' => $dto->focus,
            'score' => max(0, min(100, $dto->score)),
            'confidence' => $dto->confidence,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'rationale' => $dto->rationale,
            'baseline_metrics' => $dto->baselineMetrics,
            'observed_metrics' => $dto->observedMetrics,
            'delta_metrics' => $dto->deltaMetrics,
            'evidence' => $dto->evidence,
            'action_payload' => $dto->actionPayload,
            'personalization_context' => $dto->personalizationContext,
            'ml_features' => $dto->mlFeatures,
            'captured_at' => $dto->capturedAt ?? now(),
            'idempotency_key' => $dto->idempotencyKey,
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?OptimizationSnapshotDto
    {
        $model = OptimizationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function listByLoop(string $workspaceId, string $loopId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));

        return OptimizationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('optimization_loop_id', $loopId)
            ->orderByDesc('cycle_number')
            ->orderByDesc('captured_at')
            ->limit($limit)
            ->get()
            ->map(fn (OptimizationSnapshot $m) => $this->toDto($m))
            ->all();
    }

    public function listRecent(string $workspaceId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));

        return OptimizationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('captured_at')
            ->limit($limit)
            ->get()
            ->map(fn (OptimizationSnapshot $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(OptimizationSnapshot $m): OptimizationSnapshotDto
    {
        return new OptimizationSnapshotDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            optimizationLoopId: (string) $m->optimization_loop_id,
            cycleNumber: (int) $m->cycle_number,
            status: OptimizationSnapshotStatus::from((string) $m->status),
            engine: (string) $m->engine,
            focus: (string) $m->focus,
            score: (int) $m->score,
            confidence: $m->confidence !== null ? (float) $m->confidence : null,
            title: (string) $m->title,
            summary: (string) $m->summary,
            rationale: $m->rationale,
            baselineMetrics: is_array($m->baseline_metrics) ? $m->baseline_metrics : [],
            observedMetrics: is_array($m->observed_metrics) ? $m->observed_metrics : [],
            deltaMetrics: is_array($m->delta_metrics) ? $m->delta_metrics : [],
            evidence: is_array($m->evidence) ? $m->evidence : [],
            actionPayload: is_array($m->action_payload) ? $m->action_payload : [],
            personalizationContext: is_array($m->personalization_context) ? $m->personalization_context : [],
            mlFeatures: is_array($m->ml_features) ? $m->ml_features : [],
            capturedAt: $m->captured_at,
        );
    }
}
