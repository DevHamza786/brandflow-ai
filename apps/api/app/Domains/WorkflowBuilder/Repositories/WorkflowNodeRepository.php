<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Repositories;

use App\Domains\WorkflowBuilder\Contracts\WorkflowNodeRepositoryContract;
use App\Domains\WorkflowBuilder\Data\WorkflowNodeDto;
use App\Domains\WorkflowBuilder\Enums\WorkflowNodeType;
use App\Domains\WorkflowBuilder\Models\WorkflowNode;

final class WorkflowNodeRepository implements WorkflowNodeRepositoryContract
{
    /**
     * @return list<WorkflowNodeDto>
     */
    public function listByBlueprint(string $workspaceId, string $blueprintId): array
    {
        return WorkflowNode::query()
            ->where('workspace_id', $workspaceId)
            ->where('workflow_blueprint_id', $blueprintId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (WorkflowNode $m) => new WorkflowNodeDto(
                id: $m->id,
                workspaceId: $m->workspace_id,
                workflowBlueprintId: $m->workflow_blueprint_id,
                nodeKey: $m->node_key,
                nodeType: WorkflowNodeType::from($m->node_type),
                label: $m->label,
                config: $m->config ?? [],
                position: $m->position ?? [],
                sortOrder: (int) $m->sort_order,
            ))
            ->all();
    }
}
