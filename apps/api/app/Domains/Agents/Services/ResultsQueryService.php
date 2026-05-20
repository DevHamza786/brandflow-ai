<?php

declare(strict_types=1);

namespace App\Domains\Agents\Services;

use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Data\AgentRunResultsDto;
use App\Domains\Agents\Models\AgentRun;
use App\Domains\Agents\Support\AgentRunOutputNormalizer;
use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;
use App\Domains\Workflows\Models\WorkflowRun;
use Illuminate\Support\Facades\Log;

/**
 * Loads and normalizes agent/workflow results for API polling.
 */
final class ResultsQueryService
{
    public function __construct(
        private readonly AgentRunRepositoryContract $agentRuns,
        private readonly WorkflowRunRepositoryContract $workflowRuns,
        private readonly GeneratedOutputRepositoryContract $generatedOutputs,
        private readonly HookScoreRepositoryContract $hookScores,
        private readonly AgentRunOutputNormalizer $normalizer,
    ) {
    }

    public function getResultsForAgentRun(string $workspaceId, string $agentRunId): AgentRunResultsDto
    {
        $agentRun = $this->agentRuns->findOrFail($workspaceId, $agentRunId);
        $workflowRun = $this->resolveWorkflowRun($workspaceId, $agentRun);
        $status = $this->resolvePollingStatus($agentRun, $workflowRun);

        $outputs = $this->loadNormalizedOutputs($workspaceId, $agentRun, $workflowRun, $status);
        $aggregated = $this->normalizer->aggregatePrimaryFields($outputs);

        $metadata = $this->buildMetadata($agentRun, $workflowRun, $outputs);
        $error = $this->resolveError($agentRun, $workflowRun, $status);
        $timestamps = $this->buildTimestamps($agentRun, $workflowRun);

        Log::debug('results.query.resolved', [
            'workspace_id' => $workspaceId,
            'agent_run_id' => $agentRunId,
            'status' => $status,
            'output_count' => count($outputs),
        ]);

        return new AgentRunResultsDto(
            status: $status,
            outputs: $outputs,
            scores: $aggregated['scores'],
            metadata: $metadata,
            variants: $aggregated['variants'],
            dimensions: $aggregated['dimensions'],
            suggestions: $aggregated['suggestions'],
            error: $error,
            timestamps: $timestamps,
            agentRun: $agentRun,
            workflowRun: $workflowRun,
        );
    }

    /**
     * Pagination-ready listing (future index endpoints).
     *
     * @return array{items: list<AgentRunResultsDto>, total: int}
     */
    public function listResults(
        string $workspaceId,
        int $page = 1,
        int $perPage = 25,
    ): array {
        // Reserved for GET /api/v1/agents/runs — returns summary rows only.
        return ['items' => [], 'total' => 0];
    }

    private function resolveWorkflowRun(string $workspaceId, AgentRun $agentRun): ?WorkflowRun
    {
        $workflowRunId = (string) ($agentRun->options['workflow_run_id'] ?? '');

        if ($workflowRunId === '') {
            return null;
        }

        return $this->workflowRuns->find($workspaceId, $workflowRunId);
    }

    private function resolvePollingStatus(AgentRun $agentRun, ?WorkflowRun $workflowRun): string
    {
        if ($workflowRun !== null) {
            return match ($workflowRun->status) {
                'queued' => 'queued',
                'running', 'awaiting_approval' => 'running',
                'completed' => 'completed',
                'failed', 'cancelled' => 'failed',
                default => $this->mapAgentStatus($agentRun->status),
            };
        }

        return $this->mapAgentStatus($agentRun->status);
    }

    private function mapAgentStatus(string $status): string
    {
        return match ($status) {
            'queued' => 'queued',
            'running' => 'running',
            'completed' => 'completed',
            'failed', 'cancelled' => 'failed',
            default => 'running',
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadNormalizedOutputs(
        string $workspaceId,
        AgentRun $agentRun,
        ?WorkflowRun $workflowRun,
        string $pollingStatus,
    ): array {
        if (in_array($pollingStatus, ['queued', 'running'], true)) {
            return [];
        }

        $outputs = [];

        if ($workflowRun !== null) {
            foreach ($this->generatedOutputs->listByWorkflowRun($workspaceId, $workflowRun->id) as $dto) {
                if (! $dto->status->isTerminal()) {
                    continue;
                }

                $outputs[] = $this->normalizer->normalizeGeneratedOutput($dto);
            }
        }

        if ($outputs === []) {
            $latest = $this->generatedOutputs->findLatestByAgentRun($workspaceId, $agentRun->id);

            if ($latest !== null && $latest->status->isTerminal()) {
                $outputs[] = $this->normalizer->normalizeGeneratedOutput($latest);
            }
        }

        if ($outputs === [] && $pollingStatus === 'completed') {
            $hookScore = $this->hookScores->findByAgentRun($workspaceId, $agentRun->id);

            if ($hookScore !== null) {
                $outputs[] = $this->normalizer->normalizeHookScore($hookScore);
            }
        }

        return $outputs;
    }

    /**
     * @param  list<array<string, mixed>>  $outputs
     * @return array<string, mixed>
     */
    private function buildMetadata(
        AgentRun $agentRun,
        ?WorkflowRun $workflowRun,
        array $outputs,
    ): array {
        $primary = $outputs[0] ?? null;

        $reservedOutputId = $agentRun->options['generated_output_id'] ?? null;

        $metadata = [
            'agent' => [
                'id' => $agentRun->id,
                'slug' => $agentRun->slug,
                'status' => $agentRun->status,
            ],
            'workflow' => $workflowRun !== null ? [
                'id' => $workflowRun->id,
                'slug' => (string) ($workflowRun->context['workflow_slug'] ?? ''),
                'status' => $workflowRun->status,
                'current_step_id' => $workflowRun->current_step_id,
            ] : null,
            'provider' => $primary['provider'] ?? $agentRun->options['provider'] ?? null,
            'model' => $primary['model'] ?? $agentRun->options['model'] ?? null,
            'prompt_version' => $primary['prompt_version'] ?? null,
            'generated_output_ids' => array_values(array_unique(array_filter(array_merge(
                array_map(static fn (array $o) => $o['id'] ?? null, $outputs),
                [
                    is_array($workflowRun?->context) ? ($workflowRun->context['generated_output_id'] ?? null) : null,
                    $reservedOutputId,
                ],
            )))),
            'content_version_id' => $agentRun->input['content_version_id'] ?? null,
        ];

        if ($primary !== null && is_array($primary['metadata'] ?? null)) {
            $metadata = array_merge($metadata, $primary['metadata']);
        }

        return array_filter($metadata, static fn ($v) => $v !== null);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveError(
        AgentRun $agentRun,
        ?WorkflowRun $workflowRun,
        string $pollingStatus,
    ): ?array {
        if ($pollingStatus !== 'failed') {
            return null;
        }

        if (is_array($agentRun->error) && $agentRun->error !== []) {
            return $agentRun->error;
        }

        if ($workflowRun !== null && is_array($workflowRun->error) && $workflowRun->error !== []) {
            return $workflowRun->error;
        }

        return ['message' => 'Workflow or agent run failed.'];
    }

    /**
     * @return array<string, string|null>
     */
    private function buildTimestamps(AgentRun $agentRun, ?WorkflowRun $workflowRun): array
    {
        return [
            'created_at' => $agentRun->created_at?->toIso8601String(),
            'started_at' => $agentRun->started_at?->toIso8601String()
                ?? $workflowRun?->started_at?->toIso8601String(),
            'completed_at' => $agentRun->completed_at?->toIso8601String()
                ?? $workflowRun?->completed_at?->toIso8601String(),
            'updated_at' => $agentRun->updated_at?->toIso8601String(),
        ];
    }
}
