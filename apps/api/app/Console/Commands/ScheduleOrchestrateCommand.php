<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Schedule\Jobs\ProcessScheduledPostsJob;
use Illuminate\Console\Command;

/**
 * Dispatched by Laravel Scheduler every minute — workers handle batch claim + publish fan-out.
 */
final class ScheduleOrchestrateCommand extends Command
{
    protected $signature = 'schedule:orchestrate {--workspace=} {--limit=0 : 0 uses config(scheduling.orchestration_batch_limit)}';

    protected $description = 'Dispatch orchestration batch job for due scheduled posts';

    public function handle(): int
    {
        $workspace = $this->option('workspace');
        $workspace = is_string($workspace) && $workspace !== '' ? $workspace : null;

        $limit = (int) $this->option('limit');

        ProcessScheduledPostsJob::dispatch(
            (string) config('scheduling.orchestrator_workspace_id'),
            $workspace,
            $limit,
        );

        $this->info('ProcessScheduledPostsJob dispatched.');

        return self::SUCCESS;
    }
}
