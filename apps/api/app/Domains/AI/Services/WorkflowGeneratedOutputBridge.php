<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\AI\Contracts\WorkflowGeneratedOutputContract;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;

final class WorkflowGeneratedOutputBridge implements WorkflowGeneratedOutputContract
{
    public function __construct(
        private readonly WorkflowRunRepositoryContract $workflowRuns,
        private readonly GeneratedOutputRepositoryContract $outputs,
    ) {
    }

    public function attachToWorkflowContext(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputDto $output,
    ): void {
        $run = $this->workflowRuns->find($workspaceId, $workflowRunId);

        if ($run === null) {
            return;
        }

        $this->workflowRuns->mergeContext($run, [
            'generated_output_id' => $output->id,
            'generated_output_type' => $output->type->value,
            'agent_run_id' => $output->agentRunId ?? ($run->context['agent_run_id'] ?? null),
        ]);
    }

    public function resolveFromWorkflowContext(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputType $type,
    ): ?GeneratedOutputDto {
        $run = $this->workflowRuns->find($workspaceId, $workflowRunId);

        if ($run === null) {
            return null;
        }

        $context = $run->context ?? [];
        $outputId = $context['generated_output_id'] ?? null;

        if (is_string($outputId)) {
            $found = $this->outputs->findById($workspaceId, $outputId);

            if ($found !== null && $found->type === $type) {
                return $found;
            }
        }

        foreach ($this->outputs->listByWorkflowRun($workspaceId, $workflowRunId) as $candidate) {
            if ($candidate->type === $type) {
                return $candidate;
            }
        }

        return null;
    }
}
