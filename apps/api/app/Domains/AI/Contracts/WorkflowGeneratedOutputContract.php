<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputType;

/**
 * Workflow engine hook — store generated_output_id in workflow_runs.context.
 *
 * Expected context keys after integration:
 * - generated_output_id
 * - agent_run_id (existing)
 * - output_type
 */
interface WorkflowGeneratedOutputContract
{
    public function attachToWorkflowContext(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputDto $output,
    ): void;

    public function resolveFromWorkflowContext(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputType $type,
    ): ?GeneratedOutputDto;
}
