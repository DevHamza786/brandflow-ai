<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Repositories;

use App\Domains\WorkflowBuilder\Contracts\WorkflowEdgeRepositoryContract;
use App\Domains\WorkflowBuilder\Data\WorkflowEdgeDto;
use App\Domains\WorkflowBuilder\Enums\WorkflowEdgeType;
use App\Domains\WorkflowBuilder\Models\WorkflowEdge;

final class WorkflowEdgeRepository implements WorkflowEdgeRepositoryContract
{
    /**
     * @return list<WorkflowEdgeDto>
     */
    public function listByBlueprint(string $workspaceId, string $blueprintId): array
    {
        return WorkflowEdge::query()
            ->where('workspace_id', $workspaceId)
            ->where('workflow_blueprint_id', $blueprintId)
            ->get()
            ->map(fn (WorkflowEdge $m) => new WorkflowEdgeDto(
                id: $m->id,
                workspaceId: $m->workspace_id,
                workflowBlueprintId: $m->workflow_blueprint_id,
                fromNodeKey: $m->from_node_key,
                toNodeKey: $m->to_node_key,
                edgeType: WorkflowEdgeType::from($m->edge_type),
                condition: $m->condition,
                metadata: $m->metadata ?? [],
            ))
            ->all();
    }
}
