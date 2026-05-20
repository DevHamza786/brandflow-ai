<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\WorkflowBuilder\Contracts\WorkflowBlueprintRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowEdgeRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowNodeRepositoryContract;
use App\Domains\WorkflowBuilder\Data\WorkflowGraphDto;
use App\Domains\WorkflowBuilder\Data\WorkflowNodeDto;

/**
 * Compiles blueprint nodes/edges into an execution graph.
 */
final class WorkflowGraphOrchestrator
{
    public function __construct(
        private readonly WorkflowBlueprintRepositoryContract $blueprints,
        private readonly WorkflowNodeRepositoryContract $nodes,
        private readonly WorkflowEdgeRepositoryContract $edges,
    ) {
    }

    public function compile(string $workspaceId, string $blueprintId): WorkflowGraphDto
    {
        $blueprint = $this->blueprints->findById($workspaceId, $blueprintId)
            ?? throw new \InvalidArgumentException("Blueprint [{$blueprintId}] not found.");

        $nodeList = $this->nodes->listByBlueprint($workspaceId, $blueprintId);
        $edgeList = $this->edges->listByBlueprint($workspaceId, $blueprintId);

        $targets = [];
        foreach ($edgeList as $edge) {
            $targets[$edge->toNodeKey] = true;
        }

        $entry = [];
        foreach ($nodeList as $node) {
            if (! isset($targets[$node->nodeKey])) {
                $entry[] = $node->nodeKey;
            }
        }

        if ($entry === [] && $nodeList !== []) {
            usort($nodeList, fn (WorkflowNodeDto $a, WorkflowNodeDto $b) => $a->sortOrder <=> $b->sortOrder);
            $entry = [$nodeList[0]->nodeKey];
        }

        return new WorkflowGraphDto(
            blueprint: $blueprint,
            nodes: $nodeList,
            edges: $edgeList,
            entryNodeKeys: $entry,
        );
    }

    /**
     * Topological execution order respecting edges.
     *
     * @return list<WorkflowNodeDto>
     */
    public function executionOrder(WorkflowGraphDto $graph): array
    {
        $nodesByKey = [];
        foreach ($graph->nodes as $node) {
            $nodesByKey[$node->nodeKey] = $node;
        }

        $inDegree = array_fill_keys(array_keys($nodesByKey), 0);
        $adjacency = [];

        foreach ($graph->edges as $edge) {
            if (! isset($nodesByKey[$edge->fromNodeKey], $nodesByKey[$edge->toNodeKey])) {
                continue;
            }
            $adjacency[$edge->fromNodeKey][] = $edge->toNodeKey;
            $inDegree[$edge->toNodeKey]++;
        }

        $queue = [];
        foreach ($inDegree as $key => $degree) {
            if ($degree === 0) {
                $queue[] = $key;
            }
        }

        $order = [];
        while ($queue !== []) {
            $key = array_shift($queue);
            $order[] = $nodesByKey[$key];

            foreach ($adjacency[$key] ?? [] as $next) {
                $inDegree[$next]--;
                if ($inDegree[$next] === 0) {
                    $queue[] = $next;
                }
            }
        }

        return $order;
    }
}
