<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Listeners;

use App\Domains\Integrations\Support\IntegrationLogger;
use App\Domains\Schedule\Events\ScheduledPostPublished;
use App\Domains\Schedule\Events\ScheduledPostPublishFailed;
use App\Domains\Schedule\Events\ScheduledPostPublishingStarted;

/**
 * Structured logs for publish lifecycle (analytics pipelines can ship these later).
 */
final class LogScheduledPublishEventsListener
{
    public function __construct(
        private readonly IntegrationLogger $logger,
    ) {
    }

    public function handleStarted(ScheduledPostPublishingStarted $event): void
    {
        $this->logger->info('publishing.lifecycle.started', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->scheduledPost->workspaceId,
            'scheduled_post_id' => $event->scheduledPost->id,
            'status' => $event->scheduledPost->status->value,
        ]);
    }

    public function handlePublished(ScheduledPostPublished $event): void
    {
        $this->logger->info('publishing.lifecycle.published', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->scheduledPost->workspaceId,
            'scheduled_post_id' => $event->scheduledPost->id,
            'provider_post_id' => $event->scheduledPost->providerPostId,
        ]);
    }

    public function handleFailed(ScheduledPostPublishFailed $event): void
    {
        $this->logger->warning('publishing.lifecycle.failed', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->scheduledPost->workspaceId,
            'scheduled_post_id' => $event->scheduledPost->id,
            'error' => $event->errorDetails,
        ]);
    }
}
