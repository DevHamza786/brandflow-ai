<?php

declare(strict_types=1);

namespace App\Domains\Content\Listeners;

use App\Domains\Agents\Events\AgentRunFailed;
use App\Domains\Content\Events\HookScored;
use App\Domains\Content\Services\HookWorkflowService;

/**
 * Syncs hook_generation workflow state when scoring completes or agent fails.
 */
final class FinalizeHookGenerationWorkflow
{
    public function __construct(
        private readonly HookWorkflowService $workflow,
    ) {
    }

    public function handleHookScored(HookScored $event): void
    {
        $workflowRunId = (string) ($event->payload['workflow_run_id'] ?? '');

        if ($workflowRunId === '') {
            return;
        }

        $this->workflow->finalizeSuccess(
            workspaceId: $event->workspaceId,
            workflowRunId: $workflowRunId,
            agentRunId: $event->agentRunId,
            hookScoreId: $event->hookScoreId,
            output: [
                'hook_score_id' => $event->hookScoreId,
                'content_version_id' => $event->contentVersionId,
                'generated_output_id' => $event->generatedOutputId,
            ],
            generatedOutputId: $event->generatedOutputId,
        );
    }

    public function handleAgentRunFailed(AgentRunFailed $event): void
    {
        if ($event->agentRun->slug !== 'hook') {
            return;
        }

        $workflowRunId = (string) ($event->agentRun->options['workflow_run_id'] ?? '');

        if ($workflowRunId === '') {
            return;
        }

        $this->workflow->finalizeFailure(
            workspaceId: $event->agentRun->workspace_id,
            workflowRunId: $workflowRunId,
            agentRunId: $event->agentRun->id,
            message: $event->message,
        );
    }
}
