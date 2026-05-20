<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\WorkflowBuilder\Actions\ExecuteWorkflowBlueprintAction;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderQueryService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkflowBlueprintResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WorkflowBuilderController extends Controller
{
    use RespondsWithApiEnvelope;

    public function indexBlueprints(Request $request, WorkflowBuilderQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        return $this->success([
            'blueprints' => WorkflowBlueprintResource::collection(
                $query->listBlueprints($workspaceId),
            )->resolve($request),
        ]);
    }

    public function showBlueprint(string $blueprintId, Request $request, WorkflowBuilderQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $blueprint = $query->findBlueprint($workspaceId, $blueprintId);

        if ($blueprint === null) {
            return $this->problem(404, 'blueprint_not_found', 'Not found', 'Workflow blueprint not found.');
        }

        return $this->success([
            'blueprint' => WorkflowBlueprintResource::make($blueprint),
            'nodes' => array_map(static fn ($n) => $n->toArray(), $query->listNodes($workspaceId, $blueprintId)),
            'edges' => array_map(static fn ($e) => $e->toArray(), $query->listEdges($workspaceId, $blueprintId)),
        ]);
    }

    public function validateBlueprint(string $blueprintId, Request $request, WorkflowBuilderQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $result = $query->validateBlueprint($workspaceId, $blueprintId);

        return $this->success($result->toArray());
    }

    public function executeBlueprint(
        Request $request,
        ExecuteWorkflowBlueprintAction $action,
        ?string $blueprintId = null,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $id = $blueprintId ?? $request->input('blueprint_id');
        $result = $action->execute($workspaceId, is_string($id) ? $id : null);

        return $this->success($result->toArray(), 202);
    }
}
