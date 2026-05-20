<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Schedule\Services\SchedulerOrchestrationService;
use Illuminate\Console\Command;

/**
 * Safety net for worker crashes — re-queues publish jobs for stuck `queued` rows.
 */
final class ScheduleRecoverStaleQueuedCommand extends Command
{
    protected $signature = 'schedule:recover-stale-queued {--workspace=} {--limit=0}';

    protected $description = 'Re-dispatch PublishLinkedInPostJob for stale queued scheduled posts';

    public function handle(SchedulerOrchestrationService $orchestration): int
    {
        $workspace = $this->option('workspace');
        $workspace = is_string($workspace) && $workspace !== '' ? $workspace : null;

        $limit = (int) $this->option('limit');

        $trace = $orchestration->newTraceId();
        $count = $orchestration->recoverStaleQueued($workspace, $limit, $trace);

        $this->info("Redispatched {$count} stale queued row(s) (trace {$trace}).");

        return self::SUCCESS;
    }
}
