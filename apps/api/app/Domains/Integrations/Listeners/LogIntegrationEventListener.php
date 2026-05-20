<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Listeners;

use App\Domains\Integrations\Events\LinkedInIntegrationConnected;
use App\Domains\Integrations\Events\LinkedInIntegrationFailed;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshFailed;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshed;
use App\Domains\Integrations\Support\IntegrationLogger;

final class LogIntegrationEventListener
{
    public function __construct(
        private readonly IntegrationLogger $logger,
    ) {
    }

    public function handleConnected(LinkedInIntegrationConnected $event): void
    {
        $this->logger->info('event.connected', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->integration->workspaceId,
            'integration_id' => $event->integration->id,
            'linkedin_member_id' => $event->integration->linkedinMemberId,
        ]);
    }

    public function handleFailed(LinkedInIntegrationFailed $event): void
    {
        $this->logger->warning('event.failed', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->workspaceId,
            'message' => $event->message,
        ]);
    }

    public function handleRefreshed(LinkedInIntegrationRefreshed $event): void
    {
        $this->logger->info('event.refreshed', [
            'trace_id' => $event->traceId,
            'integration_id' => $event->integration->id,
        ]);
    }

    public function handleRefreshFailed(LinkedInIntegrationRefreshFailed $event): void
    {
        $this->logger->warning('event.refresh_failed', [
            'trace_id' => $event->traceId,
            'workspace_id' => $event->workspaceId,
            'integration_id' => $event->integrationId,
            'message' => $event->message,
        ]);
    }
}
