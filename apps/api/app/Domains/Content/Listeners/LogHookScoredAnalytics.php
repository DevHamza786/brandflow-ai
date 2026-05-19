<?php

declare(strict_types=1);

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\HookScored;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder for analytics_events ingestion (Analytics domain wires later).
 */
final class LogHookScoredAnalytics
{
    public function handle(HookScored $event): void
    {
        Log::info('analytics.event.hook_scored', [
            'workspace_id' => $event->workspaceId,
            'event_type' => 'hook.scored',
            'properties' => $event->payload,
        ]);
    }
}
