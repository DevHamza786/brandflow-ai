<?php

declare(strict_types=1);

namespace App\Queue\WorkflowJobs;

use App\Domains\Workflows\Services\WorkflowOrchestrator;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Resumes a workflow after a step completes (dispatches ready dependents).
 */
final class ResumeWorkflowRunJob extends AbstractWorkflowJob implements ShouldBeUnique
{
    public function uniqueId(): string
    {
        return $this->workflowRunId;
    }

    public function handle(WorkflowOrchestrator $orchestrator): void
    {
        $orchestrator->resume(
            workspaceId: $this->workspaceId,
            workflowRunId: $this->workflowRunId,
        );
    }
}
