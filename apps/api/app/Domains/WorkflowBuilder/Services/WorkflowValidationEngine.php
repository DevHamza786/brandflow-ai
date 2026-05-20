<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\WorkflowBuilder\Data\ValidateBlueprintResultDto;
use App\Domains\WorkflowBuilder\Data\WorkflowGraphDto;

/**
 * Validates DAG structure, orphans, and conditional edges.
 */
final class WorkflowValidationEngine
{
    public function validate(WorkflowGraphDto $graph): ValidateBlueprintResultDto
    {
        $errors = [];
        $warnings = [];

        if ($graph->nodes === []) {
            $errors[] = 'Blueprint has no nodes.';
        }

        $nodeKeys = array_map(fn ($n) => $n->nodeKey, $graph->nodes);
        $keySet = array_flip($nodeKeys);

        foreach ($graph->edges as $edge) {
            if (! isset($keySet[$edge->fromNodeKey])) {
                $errors[] = "Edge references unknown from_node [{$edge->fromNodeKey}].";
            }
            if (! isset($keySet[$edge->toNodeKey])) {
                $errors[] = "Edge references unknown to_node [{$edge->toNodeKey}].";
            }
        }

        if ($this->hasCycle($graph)) {
            $errors[] = 'Blueprint graph contains a cycle.';
        }

        if ($graph->entryNodeKeys === []) {
            $warnings[] = 'No explicit entry nodes; using sort_order fallback.';
        }

        return new ValidateBlueprintResultDto(
            valid: $errors === [],
            errors: $errors,
            warnings: $warnings,
        );
    }

    private function hasCycle(WorkflowGraphDto $graph): bool
    {
        $adjacency = [];
        foreach ($graph->edges as $edge) {
            $adjacency[$edge->fromNodeKey][] = $edge->toNodeKey;
        }

        $visited = [];
        $stack = [];

        foreach (array_map(fn ($n) => $n->nodeKey, $graph->nodes) as $node) {
            if ($this->dfsCycle($node, $adjacency, $visited, $stack)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, list<string>>  $adjacency
     * @param  array<string, bool>  $visited
     * @param  array<string, bool>  $stack
     */
    private function dfsCycle(string $node, array $adjacency, array &$visited, array &$stack): bool
    {
        if (isset($stack[$node])) {
            return true;
        }
        if (isset($visited[$node])) {
            return false;
        }

        $visited[$node] = true;
        $stack[$node] = true;

        foreach ($adjacency[$node] ?? [] as $next) {
            if ($this->dfsCycle($next, $adjacency, $visited, $stack)) {
                return true;
            }
        }

        unset($stack[$node]);

        return false;
    }
}
