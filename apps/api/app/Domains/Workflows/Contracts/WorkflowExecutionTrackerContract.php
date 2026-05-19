<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Contracts;

use App\Domains\Workflows\Data\WorkflowExecutionState;

/**
 * Redis hash storage for active workflow run orchestration.
 */
interface WorkflowExecutionTrackerContract
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function initialize(
        string $workspaceId,
        string $workflowRunId,
        string $status,
        array $context = [],
    ): void;

    public function get(string $workspaceId, string $workflowRunId): ?WorkflowExecutionState;

    public function markStepRunning(string $workspaceId, string $workflowRunId, string $stepId): void;

    public function markStepCompleted(string $workspaceId, string $workflowRunId, string $stepId): void;

    public function markStepFailed(string $workspaceId, string $workflowRunId, string $stepId, string $error): void;

    public function updateStatus(string $workspaceId, string $workflowRunId, string $status, ?string $error = null): void;

    public function forget(string $workspaceId, string $workflowRunId): void;
}
