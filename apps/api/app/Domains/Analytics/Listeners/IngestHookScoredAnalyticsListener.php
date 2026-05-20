<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Listeners;

use App\Domains\Analytics\Services\AnalyticsOrchestrationService;
use App\Domains\Content\Events\HookScored;

/**
 * Persists Hook Lab outcomes into `analytics_events` (replaces log-only placeholder).
 */
final class IngestHookScoredAnalyticsListener
{
    public function __construct(
        private readonly AnalyticsOrchestrationService $orchestration,
    ) {
    }

    public function handle(HookScored $event): void
    {
        $this->orchestration->recordHookScored(
            workspaceId: $event->workspaceId,
            contentVersionId: $event->contentVersionId,
            agentRunId: $event->agentRunId,
            hookScoreId: $event->hookScoreId,
            hookPayload: $event->payload,
            generatedOutputId: $event->generatedOutputId,
        );
    }
}
