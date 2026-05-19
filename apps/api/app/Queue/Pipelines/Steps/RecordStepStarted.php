<?php

declare(strict_types=1);

namespace App\Queue\Pipelines\Steps;

use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Queue\Pipelines\WorkflowStepContext;
use Closure;

/**
 * Pipeline stage: mark step as running in Redis tracker.
 */
final class RecordStepStarted
{
    public function __construct(
        private readonly WorkflowExecutionTrackerContract $tracker,
    ) {
    }

    public function handle(WorkflowStepContext $context, Closure $next): WorkflowStepContext
    {
        $this->tracker->markStepRunning(
            workspaceId: $context->workspaceId,
            workflowRunId: $context->workflowRunId,
            stepId: $context->stepId,
        );

        return $next($context);
    }
}
