<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Jobs;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cron-friendly sweep: enqueue per-integration refresh jobs before token expiry.
 */
final class RefreshExpiringLinkedInTokensJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(LinkedInIntegrationRepositoryContract $integrations): void
    {
        $lead = (int) config('integrations.token_refresh_lead_seconds', 3600);
        $expiring = $integrations->listExpiringBefore(now()->addSeconds($lead));

        foreach ($expiring as $integration) {
            RefreshLinkedInTokenJob::dispatch(
                $integration->workspaceId,
                $integration->id,
            );
        }
    }
}
