<?php

declare(strict_types=1);

namespace App\Domains\Content\Listeners;

use App\Domains\Agents\Events\AgentRunStarted;
use App\Domains\Content\Services\HookWorkflowService;

/**
 * Marks hook_generation workflow step when HookAgent run starts.
 */
final class TrackHookGenerationWorkflowProgress
{
    public function __construct(
        private readonly HookWorkflowService $workflow,
    ) {
    }

    public function handle(AgentRunStarted $event): void
    {
        if ($event->agentRun->slug !== 'hook') {
            return;
        }

        $this->workflow->markAgentRunning($event->agentRun->workspace_id, $event->agentRun);
    }
}
