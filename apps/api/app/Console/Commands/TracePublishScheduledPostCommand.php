<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Schedule\Jobs\PublishLinkedInPostJob;
use Illuminate\Console\Command;

/**
 * Manual verification helper — forwards to the same job workers run in production.
 */
final class TracePublishScheduledPostCommand extends Command
{
    protected $signature = 'publish:trace-linkedin {workspace_id} {scheduled_post_id}';

    protected $description = 'Dispatch a PublishLinkedInPostJob for manual queue verification';

    public function handle(): int
    {
        $workspaceId = (string) $this->argument('workspace_id');
        $scheduledPostId = (string) $this->argument('scheduled_post_id');

        PublishLinkedInPostJob::dispatch($workspaceId, $scheduledPostId);

        $this->info("Dispatched to queue [scheduling] for workspace [{$workspaceId}] post [{$scheduledPostId}].");

        return self::SUCCESS;
    }
}
