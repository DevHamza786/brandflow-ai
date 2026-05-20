<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Repositories;

use App\Domains\Coordination\Contracts\AgentCoordinationSnapshotRepositoryContract;
use App\Domains\Coordination\Data\AgentCoordinationSnapshotDto;
use App\Domains\Coordination\Data\CreateCoordinationSnapshotDto;
use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Coordination\Enums\CoordinationSnapshotStatus;
use App\Domains\Coordination\Enums\CoordinationSnapshotType;
use App\Domains\Coordination\Models\AgentCoordinationSnapshot;
use Illuminate\Support\Str;

final class AgentCoordinationSnapshotRepository implements AgentCoordinationSnapshotRepositoryContract
{
    public function create(CreateCoordinationSnapshotDto $dto): AgentCoordinationSnapshotDto
    {
        if ($dto->idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($dto->workspaceId, $dto->idempotencyKey);
            if ($existing !== null) {
                return $existing;
            }
        }

        $model = AgentCoordinationSnapshot::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'agent_coordination_id' => $dto->agentCoordinationId,
            'snapshot_type' => $dto->snapshotType->value,
            'cycle_number' => $dto->cycleNumber,
            'role_slug' => $dto->roleSlug,
            'task_type' => $dto->taskType,
            'agent_slug' => $dto->agentSlug,
            'routed_agent_slug' => $dto->routedAgentSlug,
            'handler_type' => $dto->handlerType?->value,
            'status' => $dto->status->value,
            'context_refs' => $dto->contextRefs,
            'payload' => $dto->payload,
            'error' => $dto->error,
            'idempotency_key' => $dto->idempotencyKey,
            'trace_id' => $dto->traceId,
            'agent_run_id' => $dto->agentRunId,
            'priority' => $dto->priority,
            'duration_ms' => $dto->durationMs,
        ]);

        return $this->toDto($model);
    }

    public function findByIdempotencyKey(string $workspaceId, string $idempotencyKey): ?AgentCoordinationSnapshotDto
    {
        $model = AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function findById(string $workspaceId, string $id): ?AgentCoordinationSnapshotDto
    {
        $model = AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    /**
     * @return list<AgentCoordinationSnapshotDto>
     */
    public function listByCoordination(string $workspaceId, string $coordinationId, ?int $cycle = null): array
    {
        $query = AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('agent_coordination_id', $coordinationId)
            ->orderBy('priority')
            ->orderBy('created_at');

        if ($cycle !== null) {
            $query->where('cycle_number', $cycle);
        }

        return $query->get()->map(fn ($m) => $this->toDto($m))->all();
    }

    /**
     * @return list<AgentCoordinationSnapshotDto>
     */
    public function listRecent(string $workspaceId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));

        return AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->toDto($m))
            ->all();
    }

    private function toDto(AgentCoordinationSnapshot $model): AgentCoordinationSnapshotDto
    {
        return new AgentCoordinationSnapshotDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            agentCoordinationId: $model->agent_coordination_id,
            snapshotType: CoordinationSnapshotType::from($model->snapshot_type),
            cycleNumber: (int) $model->cycle_number,
            roleSlug: $model->role_slug,
            taskType: $model->task_type,
            agentSlug: $model->agent_slug,
            routedAgentSlug: $model->routed_agent_slug,
            handlerType: $model->handler_type !== null
                ? CoordinationHandlerType::from($model->handler_type)
                : null,
            status: CoordinationSnapshotStatus::from($model->status),
            contextRefs: $model->context_refs ?? [],
            payload: $model->payload ?? [],
            error: $model->error,
            traceId: $model->trace_id,
            agentRunId: $model->agent_run_id,
            priority: (int) $model->priority,
            durationMs: $model->duration_ms,
            createdAt: $model->created_at,
        );
    }
}
