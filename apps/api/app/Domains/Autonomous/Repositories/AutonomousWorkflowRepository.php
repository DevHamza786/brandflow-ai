<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Repositories;

use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\UpdateAutonomousWorkflowDto;
use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Autonomous\Models\AutonomousWorkflow;
use Illuminate\Support\Str;

final class AutonomousWorkflowRepository implements AutonomousWorkflowRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): AutonomousWorkflowDto
    {
        $key = 'autonomous:posting:default';

        $existing = AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('correlation_key', $key)
            ->first();

        if ($existing !== null) {
            return $this->toDto($existing);
        }

        $model = AutonomousWorkflow::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'status' => AutonomousWorkflowStatus::Active->value,
            'mode' => (string) config('autonomous.default_mode', AutonomousWorkflowMode::Suggest->value),
            'correlation_key' => $key,
            'current_cycle' => 0,
            'config' => [
                'min_confidence' => (float) config('autonomous.min_confidence', 0.65),
                'min_recommendation_score' => (int) config('autonomous.min_recommendation_score', 50),
            ],
            'manual_override_enabled' => true,
            'autonomous_execution_enabled' => false,
            'started_at' => now(),
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?AutonomousWorkflowDto
    {
        $model = AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function listActive(string $workspaceId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));

        return AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', AutonomousWorkflowStatus::Active->value)
            ->orderByDesc('last_run_at')
            ->limit($limit)
            ->get()
            ->map(fn (AutonomousWorkflow $m) => $this->toDto($m))
            ->all();
    }

    public function incrementCycle(string $workspaceId, string $workflowId): AutonomousWorkflowDto
    {
        $model = AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $workflowId)
            ->firstOrFail();

        $model->update([
            'current_cycle' => $model->current_cycle + 1,
            'last_run_at' => now(),
        ]);

        return $this->toDto($model->fresh());
    }

    public function update(string $workspaceId, string $workflowId, UpdateAutonomousWorkflowDto $dto): AutonomousWorkflowDto
    {
        $model = AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $workflowId)
            ->firstOrFail();

        $patch = [];
        if ($dto->status !== null) {
            $patch['status'] = $dto->status->value;
        }
        if ($dto->mode !== null) {
            $patch['mode'] = $dto->mode->value;
        }
        if ($dto->manualOverrideEnabled !== null) {
            $patch['manual_override_enabled'] = $dto->manualOverrideEnabled;
        }
        if ($dto->autonomousExecutionEnabled !== null) {
            $patch['autonomous_execution_enabled'] = $dto->autonomousExecutionEnabled;
        }
        if ($dto->configPatch !== null || $dto->minConfidence !== null) {
            $config = is_array($model->config) ? $model->config : [];
            if ($dto->configPatch !== null) {
                $config = array_merge($config, $dto->configPatch);
            }
            if ($dto->minConfidence !== null) {
                $config['min_confidence'] = $dto->minConfidence;
            }
            $patch['config'] = $config;
        }

        if ($patch !== []) {
            $model->update($patch);
        }

        return $this->toDto($model->fresh());
    }

    public function tryAcquireLock(string $workspaceId, string $workflowId, string $lockToken): bool
    {
        $ttl = (int) config('autonomous.lock_ttl_seconds', 120);
        $staleBefore = now()->subSeconds($ttl);

        $updated = AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $workflowId)
            ->where(function ($q) use ($staleBefore): void {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<', $staleBefore);
            })
            ->update([
                'locked_at' => now(),
                'lock_token' => $lockToken,
            ]);

        return $updated === 1;
    }

    public function releaseLock(string $workspaceId, string $workflowId, string $lockToken): void
    {
        AutonomousWorkflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $workflowId)
            ->where('lock_token', $lockToken)
            ->update([
                'locked_at' => null,
                'lock_token' => null,
            ]);
    }

    private function toDto(AutonomousWorkflow $m): AutonomousWorkflowDto
    {
        return new AutonomousWorkflowDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            status: AutonomousWorkflowStatus::from((string) $m->status),
            mode: AutonomousWorkflowMode::from((string) $m->mode),
            correlationKey: (string) $m->correlation_key,
            currentCycle: (int) $m->current_cycle,
            optimizationLoopId: $m->optimization_loop_id,
            workflowRunId: $m->workflow_run_id,
            config: is_array($m->config) ? $m->config : [],
            mlState: is_array($m->ml_state) ? $m->ml_state : [],
            metadata: is_array($m->metadata) ? $m->metadata : [],
            manualOverrideEnabled: (bool) $m->manual_override_enabled,
            autonomousExecutionEnabled: (bool) $m->autonomous_execution_enabled,
            lockedAt: $m->locked_at,
            lockToken: $m->lock_token,
            startedAt: $m->started_at,
            lastRunAt: $m->last_run_at,
            completedAt: $m->completed_at,
        );
    }
}
