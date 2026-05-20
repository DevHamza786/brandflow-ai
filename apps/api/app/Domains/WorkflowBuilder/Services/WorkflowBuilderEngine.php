<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\WorkflowBuilder\Contracts\WorkflowBlueprintRepositoryContract;
use App\Domains\WorkflowBuilder\Contracts\WorkflowBuilderMlCompatibilityLayerContract;
use App\Domains\WorkflowBuilder\Data\ExecuteBlueprintResultDto;
use App\Domains\WorkflowBuilder\Events\WorkflowBlueprintExecuted;
use App\Domains\WorkflowBuilder\Events\WorkflowBlueprintValidated;
use App\Domains\WorkflowBuilder\Events\WorkflowNodeExecuted;
use App\Domains\WorkflowBuilder\Events\WorkflowNodeFailed;
use App\Domains\WorkflowBuilder\Models\WorkflowBlueprint;
use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;
use Illuminate\Support\Str;

/**
 * Validates and executes workflow blueprint graphs with per-node failure isolation.
 */
final class WorkflowBuilderEngine
{
    public function __construct(
        private readonly WorkflowBlueprintRepositoryContract $blueprints,
        private readonly WorkflowGraphOrchestrator $graph,
        private readonly WorkflowValidationEngine $validation,
        private readonly NodeExecutionEngine $nodeExecution,
        private readonly WorkflowRunRepositoryContract $workflowRuns,
        private readonly WorkflowBuilderAnalyticsIntegration $analytics,
        private readonly WorkflowBuilderOptimizationIntegration $optimization,
        private readonly WorkflowBuilderMlCompatibilityLayerContract $mlLayer,
        private readonly WorkflowExecutionLogger $logger,
    ) {
    }

    public function executeDefault(string $workspaceId): ExecuteBlueprintResultDto
    {
        $blueprint = $this->blueprints->findOrCreateDefault($workspaceId);

        return $this->execute($workspaceId, $blueprint->id);
    }

    public function execute(string $workspaceId, string $blueprintId): ExecuteBlueprintResultDto
    {
        $compiled = $this->graph->compile($workspaceId, $blueprintId);
        $validation = $this->validation->validate($compiled);

        event(new WorkflowBlueprintValidated($workspaceId, $blueprintId, $validation));

        if (! $validation->valid) {
            throw new \InvalidArgumentException('Blueprint validation failed: '.implode('; ', $validation->errors));
        }

        $traceId = 'wfb_'.Str::uuid()->toString();
        $executed = [];
        $skipped = [];
        $failed = [];

        $contextRefs = [
            'analytics' => $this->analytics->executionContextRefs($workspaceId),
            'optimization' => $this->optimization->executionContextRefs($workspaceId),
        ];

        $workflowRunId = null;
        try {
            $run = $this->workflowRuns->createQueued(
                workspaceId: $workspaceId,
                workflowId: $this->resolveLegacyWorkflowId($workspaceId),
                context: [
                    'blueprint_id' => $blueprintId,
                    'trace_id' => $traceId,
                    'context_refs' => $contextRefs,
                ],
                idempotencyKey: "blueprint:{$blueprintId}:exec:".now()->format('Y-m-d-H'),
            );
            $workflowRunId = $run->id;
        } catch (\Throwable $e) {
            $this->logger->info('workflow_run.skipped', ['reason' => $e->getMessage()]);
        }

        foreach ($this->graph->executionOrder($compiled) as $node) {
            if (! $this->shouldExecuteNode($node->nodeKey, $compiled, $executed, $failed)) {
                $skipped[] = $node->nodeKey;
                continue;
            }

            try {
                $result = $this->nodeExecution->execute($workspaceId, $node);
                $executed[] = $node->nodeKey;
                event(new WorkflowNodeExecuted($workspaceId, $blueprintId, $node->nodeKey, $result));
            } catch (\Throwable $e) {
                $failed[] = $node->nodeKey;
                event(new WorkflowNodeFailed($workspaceId, $blueprintId, $node->nodeKey, $e->getMessage()));
                $this->logger->info('node.failed.isolated', [
                    'node' => $node->nodeKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $model = WorkflowBlueprint::query()->find($blueprintId);
        if ($model !== null) {
            $model->update([
                'ml_state' => $this->mlLayer->afterExecution($model->ml_state ?? [], [
                    'executed' => count($executed),
                    'failed' => count($failed),
                ]),
            ]);
        }

        $result = new ExecuteBlueprintResultDto(
            blueprintId: $blueprintId,
            workflowRunId: $workflowRunId,
            nodesExecuted: count($executed),
            executedNodeKeys: $executed,
            skippedNodeKeys: $skipped,
            failedNodeKeys: $failed,
            traceId: $traceId,
        );

        event(new WorkflowBlueprintExecuted($workspaceId, $blueprintId, $result));

        return $result;
    }

    /**
     * @param  list<string>  $executed
     * @param  list<string>  $failed
     */
    private function shouldExecuteNode(
        string $nodeKey,
        \App\Domains\WorkflowBuilder\Data\WorkflowGraphDto $graph,
        array $executed,
        array $failed,
    ): bool {
        foreach ($graph->edges as $edge) {
            if ($edge->toNodeKey !== $nodeKey) {
                continue;
            }
            if (in_array($edge->fromNodeKey, $failed, true)) {
                return false;
            }
            if ($edge->edgeType === \App\Domains\WorkflowBuilder\Enums\WorkflowEdgeType::Conditional) {
                $branch = $edge->condition['branch'] ?? null;
                if ($branch !== null && ! in_array($edge->fromNodeKey, $executed, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function resolveLegacyWorkflowId(string $workspaceId): string
    {
        $workflow = \App\Domains\Workflows\Models\Workflow::query()
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->first();

        if ($workflow !== null) {
            return $workflow->id;
        }

        $workflow = \App\Domains\Workflows\Models\Workflow::query()->create([
            'workspace_id' => $workspaceId,
            'slug' => 'blueprint-bridge',
            'name' => 'Blueprint Bridge',
            'definition' => ['steps' => []],
            'version' => 1,
            'is_active' => true,
        ]);

        return $workflow->id;
    }
}
