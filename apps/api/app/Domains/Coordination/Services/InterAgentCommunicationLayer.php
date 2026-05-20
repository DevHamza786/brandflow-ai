<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Jobs\RunAgentJob;
use App\Domains\Coordination\Data\RoutingDecisionDto;
use App\Domains\Coordination\Data\SharedCoordinationContextDto;
use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Optimization\Actions\RunOptimizationCycleAction;
use App\Domains\Recommendations\Actions\GenerateRecommendationsAction;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches coordinated work to agents or domain integrations.
 */
final class InterAgentCommunicationLayer
{
    public function __construct(
        private readonly AgentRunRepositoryContract $agentRuns,
        private readonly AgentMemorySynchronization $memorySync,
        private readonly RunOptimizationCycleAction $runOptimization,
        private readonly GenerateRecommendationsAction $generateRecommendations,
    ) {
    }

    /**
     * @return array{status: string, agent_run_id?: string, payload: array<string, mixed>}
     */
    public function execute(
        string $workspaceId,
        RoutingDecisionDto $routing,
        SharedCoordinationContextDto $context,
        array $taskInput = [],
    ): array {
        return match ($routing->handlerType) {
            CoordinationHandlerType::Agent => $this->dispatchAgent($workspaceId, $routing, $context, $taskInput),
            CoordinationHandlerType::Optimization => $this->runOptimizationIntegration($workspaceId),
            CoordinationHandlerType::Recommendation => $this->runRecommendationIntegration($workspaceId),
            CoordinationHandlerType::Publishing => $this->runPublishingStub($workspaceId),
            CoordinationHandlerType::Deferred => [
                'status' => 'deferred',
                'payload' => ['reason' => 'handler_deferred'],
            ],
        };
    }

    /**
     * @return array{status: string, agent_run_id?: string, payload: array<string, mixed>}
     */
    private function dispatchAgent(
        string $workspaceId,
        RoutingDecisionDto $routing,
        SharedCoordinationContextDto $context,
        array $taskInput,
    ): array {
        $slug = $routing->agentSlug;
        if ($slug === null) {
            return ['status' => 'skipped', 'payload' => ['reason' => 'no_agent_slug']];
        }

        $agentClass = config("agents.agents.{$slug}.class");
        $options = $this->memorySync->mergeIntoAgentOptions($context, [
            'coordination_role' => $routing->role->value,
            'coordination_task' => $routing->taskType->value,
        ]);

        $run = $this->agentRuns->createQueued(
            workspaceId: $workspaceId,
            slug: $slug,
            input: $taskInput,
            options: $options,
        );

        if ($agentClass !== null && (bool) config('coordination.dispatch_agents', false)) {
            RunAgentJob::dispatch($workspaceId, $run->id, $slug);
            $status = 'dispatched';
        } else {
            $status = 'routed';
        }

        Log::info('coordination.agent.queued', [
            'workspace_id' => $workspaceId,
            'agent_slug' => $slug,
            'agent_run_id' => $run->id,
            'status' => $status,
        ]);

        return [
            'status' => $status,
            'agent_run_id' => $run->id,
            'payload' => ['agent_slug' => $slug],
        ];
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runOptimizationIntegration(string $workspaceId): array
    {
        try {
            $result = $this->runOptimization->execute($workspaceId);

            return [
                'status' => 'completed',
                'payload' => [
                    'optimization_loop_id' => $result->loop->id,
                    'snapshots_created' => $result->snapshotsCreated,
                ],
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runRecommendationIntegration(string $workspaceId): array
    {
        $result = $this->generateRecommendations->execute($workspaceId);

        return [
            'status' => 'completed',
            'payload' => ['recommendations_count' => $result->generatedCount],
        ];
    }

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    private function runPublishingStub(string $workspaceId): array
    {
        return [
            'status' => 'completed',
            'payload' => [
                'publish' => false,
                'ref_type' => 'schedule_stub',
                'workspace_id' => $workspaceId,
            ],
        ];
    }
}
