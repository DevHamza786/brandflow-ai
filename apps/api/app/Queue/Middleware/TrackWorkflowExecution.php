<?php

declare(strict_types=1);

namespace App\Queue\Middleware;

use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Queue\WorkflowJobs\AbstractWorkflowJob;
use Closure;
use Throwable;

/**
 * Records workflow step execution boundaries in Redis for orchestration UI.
 */
final class TrackWorkflowExecution
{
    public function __construct(
        private readonly WorkflowExecutionTrackerContract $tracker,
    ) {
    }

    public function handle(AbstractWorkflowJob $job, Closure $next): mixed
    {
        if ($job->workflowStepId === null) {
            return $next($job);
        }

        $this->tracker->markStepRunning(
            workspaceId: $job->workspaceId,
            workflowRunId: $job->workflowRunId,
            stepId: $job->workflowStepId,
        );

        try {
            $result = $next($job);

            $this->tracker->markStepCompleted(
                workspaceId: $job->workspaceId,
                workflowRunId: $job->workflowRunId,
                stepId: $job->workflowStepId,
            );

            return $result;
        } catch (Throwable $exception) {
            $this->tracker->markStepFailed(
                workspaceId: $job->workspaceId,
                workflowRunId: $job->workflowRunId,
                stepId: $job->workflowStepId,
                error: $exception->getMessage(),
            );

            throw $exception;
        }
    }
}
