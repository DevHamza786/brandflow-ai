<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\Autonomous\Actions\RunAutonomousExecutionAction;
use App\Domains\Coordination\Actions\RunCoordinationCycleAction;
use App\Domains\Optimization\Actions\RunOptimizationCycleAction;
use App\Domains\WorkflowBuilder\Data\WorkflowNodeDto;
use App\Domains\WorkflowBuilder\Enums\WorkflowNodeType;
use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Jobs\RunAgentJob;
use Illuminate\Support\Facades\Log;

/**
 * Executes a single blueprint node by type.
 */
final class NodeExecutionEngine
{
    public function __construct(
        private readonly RunCoordinationCycleAction $runCoordination,
        private readonly RunOptimizationCycleAction $runOptimization,
        private readonly RunAutonomousExecutionAction $runAutonomous,
        private readonly AgentRunRepositoryContract $agentRuns,
    ) {
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    public function execute(string $workspaceId, WorkflowNodeDto $node): array
    {
        return match ($node->nodeType) {
            WorkflowNodeType::Coordination => $this->runCoordination($workspaceId),
            WorkflowNodeType::Optimization => $this->runOptimization($workspaceId),
            WorkflowNodeType::Autonomous => $this->runAutonomous($workspaceId),
            WorkflowNodeType::Agent => $this->runAgent($workspaceId, $node),
            WorkflowNodeType::Delay => [
                'status' => 'scheduled',
                'payload' => [
                    'delay_seconds' => (int) ($node->config['delay_seconds'] ?? 0),
                    'ref_type' => 'schedule_compat',
                ],
            ],
            WorkflowNodeType::Condition => [
                'status' => 'evaluated',
                'payload' => [
                    'branch' => $node->config['default_branch'] ?? 'true',
                ],
            ],
            WorkflowNodeType::HumanGate => [
                'status' => 'awaiting_approval',
                'payload' => ['gate' => $node->nodeKey],
            ],
        };
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runCoordination(string $workspaceId): array
    {
        $result = $this->runCoordination->execute($workspaceId);

        return [
            'status' => 'completed',
            'payload' => [
                'coordination_id' => $result->coordinationId,
                'tasks_completed' => $result->tasksCompleted,
            ],
        ];
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runOptimization(string $workspaceId): array
    {
        $result = $this->runOptimization->execute($workspaceId);

        return [
            'status' => 'completed',
            'payload' => [
                'snapshots_created' => $result->snapshotsCreated,
            ],
        ];
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runAutonomous(string $workspaceId): array
    {
        $result = $this->runAutonomous->execute($workspaceId);

        return [
            'status' => 'completed',
            'payload' => [
                'snapshots_created' => $result->snapshotsCreated,
                'blocked_count' => $result->blockedCount,
            ],
        ];
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runAgent(string $workspaceId, WorkflowNodeDto $node): array
    {
        $slug = (string) ($node->config['agent_slug'] ?? 'hook');
        $run = $this->agentRuns->createQueued(
            workspaceId: $workspaceId,
            slug: $slug,
            input: $node->config['input'] ?? [],
            options: ['workflow_node_key' => $node->nodeKey],
        );

        $agentClass = config("agents.agents.{$slug}.class");
        if ($agentClass !== null && (bool) config('coordination.dispatch_agents', false)) {
            RunAgentJob::dispatch($workspaceId, $run->id, $slug);
        }

        Log::info('workflow_builder.agent.routed', [
            'workspace_id' => $workspaceId,
            'node_key' => $node->nodeKey,
            'agent_slug' => $slug,
        ]);

        return [
            'status' => 'routed',
            'payload' => ['agent_run_id' => $run->id, 'agent_slug' => $slug],
        ];
    }
}
