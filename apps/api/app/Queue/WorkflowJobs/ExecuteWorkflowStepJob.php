<?php

declare(strict_types=1);

namespace App\Queue\WorkflowJobs;

use App\Domains\Workflows\Services\WorkflowOrchestrator;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Executes a single workflow DAG step (agent, job, or human gate handoff).
 */
final class ExecuteWorkflowStepJob extends AbstractWorkflowJob implements ShouldBeUnique
{
    public function __construct(
        string $workspaceId,
        string $workflowRunId,
        public readonly string $stepId,
    ) {
        parent::__construct($workspaceId, $workflowRunId, $stepId);
    }

    public function uniqueId(): string
    {
        return "{$this->workflowRunId}:{$this->stepId}";
    }

    public function handle(WorkflowOrchestrator $orchestrator): void
    {
        $orchestrator->executeStep(
            workspaceId: $this->workspaceId,
            workflowRunId: $this->workflowRunId,
            stepId: $this->stepId,
        );
    }
}
