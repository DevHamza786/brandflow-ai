<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Coordination\Actions\RunCoordinationCycleAction;
use App\Domains\Coordination\Services\AgentRoutingEngine;
use App\Domains\Coordination\Services\CoordinationQueryService;
use App\Domains\Coordination\Data\CoordinationTaskDto;
use App\Domains\Coordination\Enums\CoordinationRole;
use App\Domains\Coordination\Enums\CoordinationTaskType;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AgentCoordinationResource;
use App\Http\Resources\Api\V1\AgentCoordinationSnapshotResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CoordinationController extends Controller
{
    use RespondsWithApiEnvelope;

    public function index(Request $request, CoordinationQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        return $this->success([
            'coordinations' => AgentCoordinationResource::collection(
                $query->listCoordinations($workspaceId),
            )->resolve($request),
        ]);
    }

    public function show(string $coordinationId, Request $request, CoordinationQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $coordination = $query->findCoordination($workspaceId, $coordinationId);

        if ($coordination === null) {
            return $this->problem(404, 'coordination_not_found', 'Not found', 'Coordination not found.');
        }

        return $this->success(AgentCoordinationResource::make($coordination));
    }

    public function indexSnapshots(Request $request, CoordinationQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $coordinationId = $request->query('coordination_id');
        $cycle = $request->query('cycle');
        $cycleNum = is_numeric($cycle) ? (int) $cycle : null;

        $snapshots = $query->listSnapshots(
            $workspaceId,
            is_string($coordinationId) ? $coordinationId : null,
            $cycleNum,
        );

        return $this->success([
            'snapshots' => AgentCoordinationSnapshotResource::collection($snapshots)->resolve($request),
        ]);
    }

    public function runCycle(Request $request, RunCoordinationCycleAction $action): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $result = $action->execute($workspaceId);

        return $this->success([
            'coordination_id' => $result->coordinationId,
            'cycle_number' => $result->cycleNumber,
            'snapshots_created' => $result->snapshotsCreated,
            'tasks_completed' => $result->tasksCompleted,
            'tasks_failed' => $result->tasksFailed,
            'tasks_recovered' => $result->tasksRecovered,
            'completed_tasks' => $result->completedTasks,
            'failed_tasks' => $result->failedTasks,
            'trace_id' => $result->traceId,
        ], 202);
    }

    public function previewRouting(Request $request, AgentRoutingEngine $routing): JsonResponse
    {
        $taskType = (string) $request->query('task_type', 'analytics_insights');
        $role = (string) $request->query('role', 'analytics');

        $task = new CoordinationTaskDto(
            taskType: CoordinationTaskType::from($taskType),
            role: CoordinationRole::from($role),
        );

        $decision = $routing->resolve($task);

        return $this->success([
            'task_type' => $decision->taskType->value,
            'role' => $decision->role->value,
            'handler_type' => $decision->handlerType->value,
            'agent_slug' => $decision->agentSlug,
            'fallback_agent_slug' => $decision->fallbackAgentSlug,
        ]);
    }
}
