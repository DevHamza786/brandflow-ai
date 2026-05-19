<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Domains\Workflows\Services\WorkflowExecutionTracker;
use App\Domains\Workflows\Services\WorkflowOrchestrator;
use App\Queue\Failed\QueueFailedJobHandler;
use App\Queue\Pipelines\Steps\RecordStepFinished;
use App\Queue\Pipelines\Steps\RecordStepStarted;
use App\Queue\Pipelines\WorkflowStepPipeline;
use App\Queue\Support\RetryPolicyResolver;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

final class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RetryPolicyResolver::class);
        $this->app->singleton(QueueFailedJobHandler::class);
        $this->app->singleton(WorkflowOrchestrator::class);

        $this->app->singleton(WorkflowExecutionTrackerContract::class, WorkflowExecutionTracker::class);

        $this->app->singleton(WorkflowStepPipeline::class, function (): WorkflowStepPipeline {
            return new WorkflowStepPipeline([
                RecordStepStarted::class,
                RecordStepFinished::class,
            ]);
        });
    }

    public function boot(): void
    {
        $this->registerQueueEventListeners();
    }

    private function registerQueueEventListeners(): void
    {
        Event::listen(JobFailed::class, function (JobFailed $event): void {
            $job = $event->job->resolveName();

            logger()->warning('queue.worker.job_failed', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $job,
                'exception' => $event->exception->getMessage(),
            ]);
        });

        Queue::after(function (): void {
            // Hook for metrics / tracing (Datadog, Nightwatch) — extend when needed.
        });
    }
}
