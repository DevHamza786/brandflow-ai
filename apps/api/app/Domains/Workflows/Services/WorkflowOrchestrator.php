<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Services;

use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Queue\WorkflowJobs\ExecuteWorkflowStepJob;
use App\Queue\WorkflowJobs\ResumeWorkflowRunJob;

/**
 * DAG orchestration: dispatch ready steps, resume after completion.
 *
 * Business step handlers are registered in a later iteration.
 */
final class WorkflowOrchestrator
{
    public function __construct(
        private readonly WorkflowExecutionTrackerContract $tracker,
    ) {
    }

    public function start(string $workspaceId, string $workflowRunId): void
    {
        $this->tracker->updateStatus($workspaceId, $workflowRunId, 'running');

        ResumeWorkflowRunJob::dispatch($workspaceId, $workflowRunId);
    }

    public function resume(string $workspaceId, string $workflowRunId): void
    {
        $state = $this->tracker->get($workspaceId, $workflowRunId);

        if ($state === null || in_array($state->status, ['completed', 'failed', 'cancelled'], true)) {
            return;
        }

        foreach ($this->resolveReadyStepIds($state) as $stepId) {
            ExecuteWorkflowStepJob::dispatch($workspaceId, $workflowRunId, $stepId);
        }
    }

    public function executeStep(string $workspaceId, string $workflowRunId, string $stepId): void
    {
        // Step-type handlers (agent, job, human_gate) will be wired here.
        $this->tracker->markStepCompleted($workspaceId, $workflowRunId, $stepId);

        ResumeWorkflowRunJob::dispatch($workspaceId, $workflowRunId);
    }

    /**
     * @return list<string>
     */
    private function resolveReadyStepIds(\App\Domains\Workflows\Data\WorkflowExecutionState $state): array
    {
        // DAG resolution from workflow definition JSON — implemented with WorkflowRepository.
        return [];
    }
}
