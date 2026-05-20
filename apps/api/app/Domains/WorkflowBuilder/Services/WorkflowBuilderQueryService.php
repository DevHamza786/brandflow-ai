<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\WorkflowBuilder\Contracts\WorkflowBlueprintRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowEdgeRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowNodeRepositoryContract;
use App\Domains\WorkflowBuilder\Data\WorkflowBlueprintDto;
use App\Domains\WorkflowBuilder\Data\WorkflowEdgeDto;
use App\Domains\WorkflowBuilder\Data\WorkflowGraphDto;
use App\Domains\WorkflowBuilder\Data\WorkflowNodeDto;
use App\Domains\WorkflowBuilder\Data\ValidateBlueprintResultDto;

final class WorkflowBuilderQueryService
{
    public function __construct(
        private readonly WorkflowBlueprintRepositoryContract $blueprints,
        private readonly WorkflowNodeRepositoryContract $nodes,
        private readonly WorkflowEdgeRepositoryContract $edges,
        private readonly WorkflowGraphOrchestrator $graph,
        private readonly WorkflowValidationEngine $validation,
    ) {
    }

    public function defaultBlueprint(string $workspaceId): WorkflowBlueprintDto
    {
        return $this->blueprints->findOrCreateDefault($workspaceId);
    }

    public function findBlueprint(string $workspaceId, string $id): ?WorkflowBlueprintDto
    {
        return $this->blueprints->findById($workspaceId, $id);
    }

    /**
     * @return list<WorkflowBlueprintDto>
     */
    public function listBlueprints(string $workspaceId): array
    {
        $this->blueprints->findOrCreateDefault($workspaceId);

        return $this->blueprints->listActive($workspaceId);
    }

    /**
     * @return list<WorkflowNodeDto>
     */
    public function listNodes(string $workspaceId, string $blueprintId): array
    {
        return $this->nodes->listByBlueprint($workspaceId, $blueprintId);
    }

    /**
     * @return list<WorkflowEdgeDto>
     */
    public function listEdges(string $workspaceId, string $blueprintId): array
    {
        return $this->edges->listByBlueprint($workspaceId, $blueprintId);
    }

    public function compileGraph(string $workspaceId, string $blueprintId): WorkflowGraphDto
    {
        return $this->graph->compile($workspaceId, $blueprintId);
    }

    public function validateBlueprint(string $workspaceId, string $blueprintId): ValidateBlueprintResultDto
    {
        return $this->validation->validate($this->graph->compile($workspaceId, $blueprintId));
    }
}
