<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Repositories;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationLoopStatus;
use App\Domains\Optimization\Enums\OptimizationLoopType;
use App\Domains\Optimization\Models\OptimizationLoop;
use Illuminate\Support\Str;

final class OptimizationLoopRepository implements OptimizationLoopRepositoryContract
{
    public function findOrCreateActive(string $workspaceId, OptimizationLoopType $type): OptimizationLoopDto
    {
        $key = 'loop:'.$type->value;

        $existing = OptimizationLoop::query()
            ->where('workspace_id', $workspaceId)
            ->where('correlation_key', $key)
            ->where('status', OptimizationLoopStatus::Active->value)
            ->first();

        if ($existing !== null) {
            return $this->toDto($existing);
        }

        $model = OptimizationLoop::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'loop_type' => $type->value,
            'status' => OptimizationLoopStatus::Active->value,
            'correlation_key' => $key,
            'current_cycle' => 0,
            'config' => [
                'lookback_days' => (int) config('optimization.lookback_days', 30),
                'comparison_days' => (int) config('optimization.comparison_days', 30),
            ],
            'started_at' => now(),
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?OptimizationLoopDto
    {
        $model = OptimizationLoop::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function incrementCycle(string $workspaceId, string $loopId): OptimizationLoopDto
    {
        $model = OptimizationLoop::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $loopId)
            ->firstOrFail();

        $model->update([
            'current_cycle' => $model->current_cycle + 1,
            'last_run_at' => now(),
        ]);

        return $this->toDto($model->fresh());
    }

    public function listActive(string $workspaceId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));

        return OptimizationLoop::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', OptimizationLoopStatus::Active->value)
            ->orderByDesc('last_run_at')
            ->limit($limit)
            ->get()
            ->map(fn (OptimizationLoop $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(OptimizationLoop $m): OptimizationLoopDto
    {
        return new OptimizationLoopDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            loopType: OptimizationLoopType::from((string) $m->loop_type),
            status: OptimizationLoopStatus::from((string) $m->status),
            correlationKey: (string) $m->correlation_key,
            currentCycle: (int) $m->current_cycle,
            config: is_array($m->config) ? $m->config : [],
            mlState: is_array($m->ml_state) ? $m->ml_state : [],
            metadata: is_array($m->metadata) ? $m->metadata : [],
            startedAt: $m->started_at,
            lastRunAt: $m->last_run_at,
            completedAt: $m->completed_at,
        );
    }
}
