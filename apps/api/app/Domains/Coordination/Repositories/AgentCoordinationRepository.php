<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Repositories;

use App\Domains\Coordination\Contracts\AgentCoordinationRepositoryContract;
use App\Domains\Coordination\Data\AgentCoordinationDto;
use App\Domains\Coordination\Enums\CoordinationMode;
use App\Domains\Coordination\Enums\CoordinationStatus;
use App\Domains\Coordination\Models\AgentCoordination;
use Illuminate\Support\Str;

final class AgentCoordinationRepository implements AgentCoordinationRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): AgentCoordinationDto
    {
        $key = (string) config('coordination.default_correlation_key', 'coordination:workspace:default');

        $existing = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('correlation_key', $key)
            ->whereIn('status', [
                CoordinationStatus::Active->value,
                CoordinationStatus::Running->value,
                CoordinationStatus::PartialSuccess->value,
            ])
            ->first();

        if ($existing !== null) {
            return $this->toDto($existing);
        }

        $model = AgentCoordination::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'status' => CoordinationStatus::Active->value,
            'coordination_mode' => CoordinationMode::Sequential->value,
            'correlation_key' => $key,
            'current_cycle' => 0,
            'shared_context' => [],
            'config' => [
                'failure_isolation' => (bool) config('coordination.failure_isolation', true),
            ],
            'started_at' => now(),
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?AgentCoordinationDto
    {
        $model = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function incrementCycle(string $workspaceId, string $coordinationId): AgentCoordinationDto
    {
        $model = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $coordinationId)
            ->firstOrFail();

        $model->update([
            'current_cycle' => $model->current_cycle + 1,
            'last_run_at' => now(),
            'status' => CoordinationStatus::Running->value,
        ]);

        return $this->toDto($model->fresh());
    }

    public function updateSharedContext(
        string $workspaceId,
        string $coordinationId,
        array $sharedContext,
    ): AgentCoordinationDto {
        $model = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $coordinationId)
            ->firstOrFail();

        $model->update(['shared_context' => $sharedContext]);

        return $this->toDto($model->fresh());
    }

    public function tryAcquireLock(string $workspaceId, string $coordinationId, string $lockToken): bool
    {
        $updated = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $coordinationId)
            ->where(function ($q): void {
                $q->whereNull('lock_token')
                    ->orWhere('locked_at', '<', now()->subMinutes(5));
            })
            ->update([
                'lock_token' => $lockToken,
                'locked_at' => now(),
            ]);

        return $updated === 1;
    }

    public function releaseLock(string $workspaceId, string $coordinationId, string $lockToken): void
    {
        AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $coordinationId)
            ->where('lock_token', $lockToken)
            ->update([
                'lock_token' => null,
                'locked_at' => null,
            ]);
    }

    public function updateStatus(string $workspaceId, string $coordinationId, string $status): AgentCoordinationDto
    {
        $model = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $coordinationId)
            ->firstOrFail();

        $model->update(['status' => $status]);

        return $this->toDto($model->fresh());
    }

    /**
     * @return list<AgentCoordinationDto>
     */
    public function listRecent(string $workspaceId, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));

        return AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('last_run_at')
            ->limit($limit)
            ->get()
            ->map(fn (AgentCoordination $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(AgentCoordination $model): AgentCoordinationDto
    {
        return new AgentCoordinationDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            status: CoordinationStatus::from($model->status),
            coordinationMode: CoordinationMode::from($model->coordination_mode),
            correlationKey: $model->correlation_key,
            currentCycle: (int) $model->current_cycle,
            workflowRunId: $model->workflow_run_id,
            workflowBlueprintId: $model->workflow_blueprint_id,
            optimizationLoopId: $model->optimization_loop_id,
            autonomousWorkflowId: $model->autonomous_workflow_id,
            sharedContext: $model->shared_context ?? [],
            config: $model->config ?? [],
            mlState: $model->ml_state ?? [],
            metadata: $model->metadata ?? [],
            startedAt: $model->started_at ?? $model->created_at,
            lastRunAt: $model->last_run_at,
            completedAt: $model->completed_at,
        );
    }
}
