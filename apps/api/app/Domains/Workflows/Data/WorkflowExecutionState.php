<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Redis-backed workflow run state (mirrors workflow_runs + ephemeral progress).
 */
final class WorkflowExecutionState extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $context
     * @param  list<string>  $completedSteps
     */
    public function __construct(
        public readonly string $workflowRunId,
        public readonly string $workspaceId,
        public readonly string $status,
        public readonly array $context = [],
        public readonly array $completedSteps = [],
        public readonly ?string $currentStepId = null,
        public readonly ?string $error = null,
    ) {
    }

    public function isStepCompleted(string $stepId): bool
    {
        return in_array($stepId, $this->completedSteps, true);
    }
}
