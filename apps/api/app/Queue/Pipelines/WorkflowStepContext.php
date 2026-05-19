<?php

declare(strict_types=1);

namespace App\Queue\Pipelines;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Passable context for workflow step execution pipeline.
 */
final class WorkflowStepContext extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $stepDefinition
     * @param  array<string, mixed>  $runContext
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $workflowRunId,
        public readonly string $stepId,
        public readonly array $stepDefinition,
        public readonly array $runContext = [],
        public readonly ?string $error = null,
    ) {
    }

    public function stepType(): ?string
    {
        return isset($this->stepDefinition['type']) ? (string) $this->stepDefinition['type'] : null;
    }
}
