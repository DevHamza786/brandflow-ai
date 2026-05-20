<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Autonomous\Actions\RunAutonomousExecutionAction;
use App\Domains\Autonomous\Actions\UpdateAutonomousWorkflowAction;
use App\Domains\Autonomous\Data\UpdateAutonomousWorkflowDto;
use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Autonomous\Services\AutonomousQueryService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateAutonomousWorkflowRequest;
use App\Http\Resources\Api\V1\AutonomousExecutionSnapshotResource;
use App\Http\Resources\Api\V1\AutonomousWorkflowResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AutonomousController extends Controller
{
    use RespondsWithApiEnvelope;

    public function indexWorkflows(Request $request, AutonomousQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $workflows = $query->listWorkflows($workspaceId);
        if ($workflows === []) {
            $workflows = [$query->defaultWorkflow($workspaceId)];
        }

        return $this->success([
            'workflows' => AutonomousWorkflowResource::collection($workflows)->resolve($request),
        ]);
    }

    public function showWorkflow(string $workflowId, Request $request, AutonomousQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $workflow = $query->findWorkflow($workspaceId, $workflowId);

        if ($workflow === null) {
            return $this->problem(404, 'autonomous_workflow_not_found', 'Not found', 'Autonomous workflow not found.');
        }

        return $this->success(AutonomousWorkflowResource::make($workflow));
    }

    public function updateWorkflow(
        string $workflowId,
        UpdateAutonomousWorkflowRequest $request,
        UpdateAutonomousWorkflowAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $dto = new UpdateAutonomousWorkflowDto(
            status: $request->validated('status') !== null
                ? AutonomousWorkflowStatus::from((string) $request->validated('status'))
                : null,
            mode: $request->validated('mode') !== null
                ? AutonomousWorkflowMode::from((string) $request->validated('mode'))
                : null,
            manualOverrideEnabled: $request->has('manual_override_enabled')
                ? (bool) $request->boolean('manual_override_enabled')
                : null,
            autonomousExecutionEnabled: $request->has('autonomous_execution_enabled')
                ? (bool) $request->boolean('autonomous_execution_enabled')
                : null,
            minConfidence: $request->minConfidence(),
        );

        $workflow = $action->execute($workspaceId, $workflowId, $dto);

        return $this->success(AutonomousWorkflowResource::make($workflow));
    }

    public function indexSnapshots(Request $request, AutonomousQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $workflowId = $request->query('workflow_id');
        $snapshots = $query->listSnapshots(
            $workspaceId,
            is_string($workflowId) && $workflowId !== '' ? $workflowId : null,
        );

        return $this->success([
            'snapshots' => AutonomousExecutionSnapshotResource::collection($snapshots)->resolve($request),
        ]);
    }

    public function showSnapshot(string $snapshotId, Request $request, AutonomousQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $snapshot = $query->findSnapshot($workspaceId, $snapshotId);

        if ($snapshot === null) {
            return $this->problem(404, 'autonomous_snapshot_not_found', 'Not found', 'Autonomous snapshot not found.');
        }

        return $this->success(AutonomousExecutionSnapshotResource::make($snapshot));
    }

    public function runExecution(
        Request $request,
        RunAutonomousExecutionAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $result = $action->execute($workspaceId);

        return $this->success([
            'workflow' => AutonomousWorkflowResource::make($result->workflow)->resolve($request),
            'cycle_number' => $result->cycleNumber,
            'snapshots_created' => $result->snapshotsCreated,
            'blocked_count' => $result->blockedCount,
            'approved_count' => $result->approvedCount,
            'counts_by_status' => $result->countsByStatus,
            'snapshots' => AutonomousExecutionSnapshotResource::collection($result->snapshots)->resolve($request),
        ], 202);
    }
}
