<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\Exceptions\HookContentNotFoundException;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Models\AgentRun;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\Content\Actions\GenerateHooksAction;
use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Data\HookGenerationResultDto;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;
use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Domains\Workflows\Contracts\WorkflowRepositoryContract;
use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;
use App\Domains\Workflows\Models\WorkflowRun;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates hook_generation workflow: DB run + Redis tracker + HookAgent queue.
 */
final class HookWorkflowService
{
    private const WORKFLOW_SLUG = 'hook_generation';

    public function __construct(
        private readonly WorkflowRepositoryContract $workflows,
        private readonly WorkflowRunRepositoryContract $workflowRuns,
        private readonly WorkflowExecutionTrackerContract $tracker,
        private readonly ContentVersionRepositoryContract $contentVersions,
        private readonly AgentRunRepositoryContract $agentRuns,
        private readonly GenerateHooksAction $generateHooks,
        private readonly HookScoreRepositoryContract $hookScores,
        private readonly HookGeneratedOutputPersistenceService $hookGeneratedOutputs,
    ) {
    }

    public function start(
        string $workspaceId,
        HookAgentConfig $config,
        ?string $idempotencyKey = null,
    ): HookGenerationResultDto {
        if ($this->contentVersions->findForWorkspace($workspaceId, $config->contentVersionId) === null) {
            throw new HookContentNotFoundException(
                "Content version [{$config->contentVersionId}] not found in this workspace.",
                ['workspace_id' => $workspaceId],
            );
        }

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $existing = $this->resolveIdempotentRun($workspaceId, $idempotencyKey, $config->contentVersionId);

            if ($existing !== null) {
                return $existing;
            }
        }

        $workflow = $this->workflows->ensureBuiltIn($workspaceId, self::WORKFLOW_SLUG);

        try {
            $workflowRun = $this->workflowRuns->createQueued(
                workspaceId: $workspaceId,
                workflowId: $workflow->id,
                context: [
                    'workflow_slug' => self::WORKFLOW_SLUG,
                    'content_version_id' => $config->contentVersionId,
                    'agent_slug' => 'hook',
                ],
                idempotencyKey: $idempotencyKey,
            );
        } catch (UniqueConstraintViolationException $e) {
            $existing = $this->workflowRuns->findByIdempotencyKey($workspaceId, $idempotencyKey);

            if ($existing !== null) {
                return $this->buildResultFromWorkflowRun($existing, true);
            }

            throw $e;
        }

        $this->initializeTracker($workspaceId, $workflowRun, $config);

        $this->workflowRuns->markRunning($workflowRun, 'dispatch_agent');
        $this->tracker->markStepCompleted($workspaceId, $workflowRun->id, 'validate_input');
        $this->tracker->markStepRunning($workspaceId, $workflowRun->id, 'dispatch_agent');

        $agentRun = $this->generateHooks->execute(
            workspaceId: $workspaceId,
            config: $config,
            workflowRunId: $workflowRun->id,
            idempotencyKey: $idempotencyKey,
        );

        $agentContext = new AgentContext(
            workspaceId: $workspaceId,
            agentRunId: $agentRun->id,
            slug: 'hook',
            input: $agentRun->input ?? [],
            options: $agentRun->options ?? [],
        );

        $generatedOutput = $this->hookGeneratedOutputs->reserveForWorkflow(
            $agentContext,
            $config,
            $workflowRun->id,
        );

        $agentRun = $this->agentRuns->mergeOptions($agentRun, [
            'generated_output_id' => $generatedOutput->id,
        ]);

        $workflowRun = $this->workflowRuns->mergeContext($workflowRun, [
            'agent_run_id' => $agentRun->id,
            'generated_output_id' => $generatedOutput->id,
        ]);

        $this->tracker->markStepCompleted($workspaceId, $workflowRun->id, 'dispatch_agent');
        $this->tracker->markStepRunning($workspaceId, $workflowRun->id, 'run_hook_agent');

        Log::info('hook.workflow.started', [
            'workspace_id' => $workspaceId,
            'workflow_run_id' => $workflowRun->id,
            'agent_run_id' => $agentRun->id,
            'generated_output_id' => $generatedOutput->id,
            'content_version_id' => $config->contentVersionId,
        ]);

        return new HookGenerationResultDto(
            agentRun: $agentRun,
            workflowRun: $workflowRun->fresh() ?? $workflowRun,
            wasReplayed: false,
        );
    }

    public function markAgentRunning(string $workspaceId, AgentRun $agentRun): void
    {
        $workflowRunId = (string) ($agentRun->options['workflow_run_id'] ?? '');

        if ($workflowRunId === '') {
            return;
        }

        $this->tracker->markStepRunning($workspaceId, $workflowRunId, 'run_hook_agent');
    }

    /**
     * @param  array<string, mixed>  $output
     */
    public function finalizeSuccess(
        string $workspaceId,
        string $workflowRunId,
        string $agentRunId,
        ?string $hookScoreId,
        array $output = [],
        ?string $generatedOutputId = null,
    ): void {
        $workflowRun = $this->workflowRuns->findOrFail($workspaceId, $workflowRunId);

        $this->tracker->markStepCompleted($workspaceId, $workflowRunId, 'run_hook_agent');
        $this->tracker->markStepCompleted($workspaceId, $workflowRunId, 'persist_results');
        $this->tracker->updateStatus($workspaceId, $workflowRunId, 'completed');

        $this->workflowRuns->markCompleted($workflowRun, array_filter([
            'agent_run_id' => $agentRunId,
            'hook_score_id' => $hookScoreId,
            'generated_output_id' => $generatedOutputId,
            'output' => $output,
        ], static fn ($v) => $v !== null));

        Log::info('hook.workflow.completed', [
            'workspace_id' => $workspaceId,
            'workflow_run_id' => $workflowRunId,
            'agent_run_id' => $agentRunId,
            'hook_score_id' => $hookScoreId,
            'generated_output_id' => $generatedOutputId,
        ]);
    }

    public function finalizeFailure(
        string $workspaceId,
        string $workflowRunId,
        string $agentRunId,
        string $message,
    ): void {
        $workflowRun = $this->workflowRuns->find($workspaceId, $workflowRunId);

        if ($workflowRun === null) {
            return;
        }

        $stepId = $workflowRun->current_step_id ?? 'run_hook_agent';

        $this->tracker->markStepFailed($workspaceId, $workflowRunId, $stepId, $message);
        $this->tracker->updateStatus($workspaceId, $workflowRunId, 'failed', $message);

        $this->workflowRuns->markFailed($workflowRun, $message, [
            'agent_run_id' => $agentRunId,
        ]);

        Log::error('hook.workflow.failed', [
            'workspace_id' => $workspaceId,
            'workflow_run_id' => $workflowRunId,
            'agent_run_id' => $agentRunId,
            'message' => $message,
        ]);
    }

    public function getResult(
        string $workspaceId,
        string $agentRunId,
    ): HookGenerationResultDto {
        $agentRun = $this->agentRuns->findOrFail($workspaceId, $agentRunId);

        $workflowRunId = (string) ($agentRun->options['workflow_run_id'] ?? '');

        if ($workflowRunId === '') {
            throw ValidationException::withMessages([
                'agent_run_id' => ['Agent run is not linked to a hook generation workflow.'],
            ]);
        }

        $workflowRun = $this->workflowRuns->findOrFail($workspaceId, $workflowRunId);

        $hookScore = $agentRun->status === 'completed'
            ? $this->hookScores->findByAgentRun($workspaceId, $agentRunId)
            : null;

        return new HookGenerationResultDto(
            agentRun: $agentRun,
            workflowRun: $workflowRun,
            wasReplayed: false,
            hookScore: $hookScore,
        );
    }

    private function resolveIdempotentRun(
        string $workspaceId,
        string $idempotencyKey,
        string $contentVersionId,
    ): ?HookGenerationResultDto {
        $workflowRun = $this->workflowRuns->findByIdempotencyKey($workspaceId, $idempotencyKey);

        if ($workflowRun !== null) {
            $storedVersion = (string) ($workflowRun->context['content_version_id'] ?? '');

            if ($storedVersion !== '' && $storedVersion !== $contentVersionId) {
                throw ValidationException::withMessages([
                    'idempotency_key' => ['Idempotency key was already used for a different content version.'],
                ]);
            }

            return $this->buildResultFromWorkflowRun($workflowRun, true);
        }

        $agentRun = $this->agentRuns->findByIdempotencyKey($workspaceId, $idempotencyKey);

        if ($agentRun !== null) {
            $storedVersion = (string) ($agentRun->input['content_version_id'] ?? '');

            if ($storedVersion !== '' && $storedVersion !== $contentVersionId) {
                throw ValidationException::withMessages([
                    'idempotency_key' => ['Idempotency key was already used for a different content version.'],
                ]);
            }

            return $this->getResult($workspaceId, $agentRun->id);
        }

        return null;
    }

    private function buildResultFromWorkflowRun(
        WorkflowRun $workflowRun,
        bool $wasReplayed,
        ?AgentRun $agentRun = null,
    ): HookGenerationResultDto {
        $agentRunId = (string) ($workflowRun->context['agent_run_id'] ?? '');

        if ($agentRun === null && $agentRunId !== '') {
            $agentRun = $this->agentRuns->findOrFail($workflowRun->workspace_id, $agentRunId);
        }

        if ($agentRun === null) {
            throw ValidationException::withMessages([
                'workflow_run' => ['Workflow run has no linked agent run yet.'],
            ]);
        }

        return new HookGenerationResultDto(
            agentRun: $agentRun,
            workflowRun: $workflowRun,
            wasReplayed: $wasReplayed,
        );
    }

    private function initializeTracker(
        string $workspaceId,
        WorkflowRun $workflowRun,
        HookAgentConfig $config,
    ): void {
        $this->tracker->initialize(
            workspaceId: $workspaceId,
            workflowRunId: $workflowRun->id,
            status: 'queued',
            context: [
                'workflow_slug' => self::WORKFLOW_SLUG,
                'content_version_id' => $config->contentVersionId,
                'agent_slug' => 'hook',
                'steps' => ['validate_input', 'dispatch_agent', 'run_hook_agent', 'persist_results'],
            ],
        );

        $this->tracker->markStepCompleted($workspaceId, $workflowRun->id, 'validate_input');
    }
}
