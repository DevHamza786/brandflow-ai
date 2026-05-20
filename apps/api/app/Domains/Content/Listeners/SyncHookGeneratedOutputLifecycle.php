<?php

declare(strict_types=1);

namespace App\Domains\Content\Listeners;

use App\Domains\Agents\Events\AgentRunFailed;
use App\Domains\Agents\Events\AgentRunStarted;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;
use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;

/**
 * Marks generated_outputs processing / failed in sync with agent run lifecycle.
 */
final class SyncHookGeneratedOutputLifecycle
{
    public function __construct(
        private readonly HookGeneratedOutputPersistenceService $persistence,
        private readonly GeneratedOutputRepositoryContract $outputs,
    ) {
    }

    public function handleAgentRunStarted(AgentRunStarted $event): void
    {
        if ($event->agentRun->slug !== 'hook') {
            return;
        }

        $generatedOutputId = (string) ($event->agentRun->options['generated_output_id'] ?? '');

        if ($generatedOutputId === '') {
            $workflowRunId = (string) ($event->agentRun->options['workflow_run_id'] ?? '');

            if ($workflowRunId !== '') {
                $found = $this->outputs->findByWorkflowRun(
                    $event->agentRun->workspace_id,
                    $workflowRunId,
                    GeneratedOutputType::Hook,
                );
                $generatedOutputId = $found?->id ?? '';
            }
        }

        if ($generatedOutputId === '') {
            return;
        }

        $this->persistence->markProcessing(
            $event->agentRun->workspace_id,
            $generatedOutputId,
        );
    }

    public function handleAgentRunFailed(AgentRunFailed $event): void
    {
        if ($event->agentRun->slug !== 'hook') {
            return;
        }

        $generatedOutputId = (string) ($event->agentRun->options['generated_output_id'] ?? '');
        $workflowRunId = (string) ($event->agentRun->options['workflow_run_id'] ?? '');

        if ($generatedOutputId === '' && $workflowRunId !== '') {
            $found = $this->outputs->findByWorkflowRun(
                $event->agentRun->workspace_id,
                $workflowRunId,
                GeneratedOutputType::Hook,
            );
            $generatedOutputId = $found?->id ?? '';
        }

        if ($generatedOutputId === '') {
            return;
        }

        $this->persistence->markFailed(
            $event->agentRun->workspace_id,
            $generatedOutputId,
            [
                'message' => $event->message,
                'agent_run_id' => $event->agentRun->id,
            ],
            $workflowRunId !== '' ? $workflowRunId : null,
        );
    }
}
