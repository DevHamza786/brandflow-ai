<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Repositories\ScheduledPostRepository;
use App\Domains\Schedule\Support\ScheduledPostNormalizer;
use App\Domains\Shared\Services\WorkspaceBootstrapService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WorkspaceBootstrapService::class);

        /*
         * Schedule repository bindings are duplicated from ScheduleServiceProvider so queue workers
         * (Horizon) and any bootstrap path always resolve PublishLinkedInPostJob dependencies.
         * @see https://laravel.com/docs/container#binding-interfaces-to-implementations
         */
        $this->app->singleton(ScheduledPostNormalizer::class);
        $this->app->bind(ScheduledPostRepositoryContract::class, ScheduledPostRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
