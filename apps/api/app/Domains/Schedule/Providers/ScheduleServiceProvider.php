<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Providers;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Actions\PublishToLinkedInAction;
use App\Domains\Schedule\Actions\SchedulePostAction;
use App\Domains\Schedule\Listeners\LogScheduledPublishEventsListener;
use App\Domains\Schedule\Repositories\ScheduledPostRepository;
use App\Domains\Schedule\Events\ScheduledPostPublished;
use App\Domains\Schedule\Events\ScheduledPostPublishFailed;
use App\Domains\Schedule\Events\ScheduledPostPublishingStarted;
use App\Domains\Schedule\Pipelines\ScheduleExecutionPipeline;
use App\Domains\Schedule\Services\LinkedInPublishingService;
use App\Domains\Schedule\Services\PublishingWorkflowIntegration;
use App\Domains\Schedule\Services\ScheduleExecutionLogger;
use App\Domains\Schedule\Services\SchedulerOrchestrationService;
use App\Domains\Schedule\Support\ScheduledPostNormalizer;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\Event;

final class ScheduleServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Schedule';
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(ScheduledPostNormalizer::class);
        $this->app->bind(ScheduledPostRepositoryContract::class, ScheduledPostRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(LinkedInPublishingService::class);
        $this->app->singleton(ScheduleExecutionLogger::class);
        $this->app->singleton(ScheduleExecutionPipeline::class);
        $this->app->singleton(SchedulerOrchestrationService::class);
        $this->app->singleton(SchedulePostAction::class);
        $this->app->singleton(PublishToLinkedInAction::class);
        $this->app->singleton(PublishingWorkflowIntegration::class);
    }

    public function boot(): void
    {
        $listener = LogScheduledPublishEventsListener::class;

        Event::listen(ScheduledPostPublishingStarted::class, [$listener, 'handleStarted']);
        Event::listen(ScheduledPostPublished::class, [$listener, 'handlePublished']);
        Event::listen(ScheduledPostPublishFailed::class, [$listener, 'handleFailed']);
    }
}
