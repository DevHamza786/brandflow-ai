<?php

declare(strict_types=1);

namespace App\Domains\Agents\Services;

use App\Domains\Agents\Contracts\AgentContract;
use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\Agents\Data\AgentResult;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Executes agent runs loaded from agent_runs table.
 */
final class AgentRunner
{
    public function __construct(
        private readonly AgentRunRepositoryContract $runs,
        private readonly Container $container,
    ) {
    }

    public function run(string $slug, string $workspaceId, string $agentRunId): AgentResult
    {
        $run = $this->runs->findOrFail($workspaceId, $agentRunId);

        if ($run->slug !== $slug) {
            throw new \InvalidArgumentException(
                "Agent run [{$agentRunId}] slug [{$run->slug}] does not match [{$slug}]."
            );
        }

        if (in_array($run->status, ['completed', 'cancelled'], true)) {
            Log::info('agent.run.skipped', [
                'agent_run_id' => $agentRunId,
                'status' => $run->status,
            ]);

            return new AgentResult(
                output: $run->output ?? [],
                summary: 'Run already terminal.',
                traceId: $run->trace_id,
            );
        }

        $agent = $this->resolveAgent($slug);

        $context = new AgentContext(
            workspaceId: $workspaceId,
            agentRunId: $agentRunId,
            slug: $slug,
            input: $run->input ?? [],
            options: $run->options ?? [],
        );

        $this->runs->markRunning($run);

        try {
            $result = $agent->run($context);
            $this->runs->markCompleted($run, $result);

            return $result;
        } catch (Throwable $e) {
            $this->runs->markFailed($run, $e->getMessage(), [
                'exception' => $e::class,
            ]);

            throw $e;
        }
    }

    private function resolveAgent(string $slug): AgentContract
    {
        $binding = "agent.{$slug}";

        if (! $this->container->bound($binding)) {
            throw new \InvalidArgumentException("Agent [{$slug}] is not registered.");
        }

        return $this->container->make($binding);
    }
}
