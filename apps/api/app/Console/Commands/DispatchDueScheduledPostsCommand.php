<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Schedule\Jobs\ProcessScheduledPostsJob;
use Illuminate\Console\Command;

/**
 * @deprecated Use {@see ScheduleOrchestrateCommand} (`schedule:orchestrate`) for production orchestration.
 *             This command now forwards to the queue-driven batch job (claim + fan-out).
 */
final class DispatchDueScheduledPostsCommand extends Command
{
    protected $signature = 'schedule:dispatch-due {--workspace=} {--limit=100}';

    protected $description = 'Forward to ProcessScheduledPostsJob (atomic claim + publish fan-out)';

    public function handle(): int
    {
        $workspace = $this->option('workspace') !== null && $this->option('workspace') !== ''
            ? (string) $this->option('workspace')
            : null;

        $limit = max(1, (int) $this->option('limit'));

        ProcessScheduledPostsJob::dispatch(
            (string) config('scheduling.orchestrator_workspace_id'),
            $workspace,
            $limit,
        );

        $this->warn('schedule:dispatch-due is deprecated — use schedule:orchestrate. Orchestration job dispatched.');
        $this->info('ProcessScheduledPostsJob dispatched.');

        return self::SUCCESS;
    }
}
