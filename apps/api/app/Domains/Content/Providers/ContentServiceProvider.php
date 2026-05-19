<?php

declare(strict_types=1);

namespace App\Domains\Content\Providers;

use App\Domains\Content\Actions\GenerateHooksAction;
use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Events\HookScored;
use App\Domains\Content\Listeners\LogHookScoredAnalytics;
use App\Domains\Content\Repositories\ContentVersionRepository;
use App\Domains\Content\Repositories\HookScoreRepository;
use App\Domains\Content\Services\HookGenerationService;
use App\Domains\Content\Services\HookScoringService;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\Event;

final class ContentServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Content';
    }

    public function boot(): void
    {
        Event::listen(HookScored::class, LogHookScoredAnalytics::class);
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(ContentVersionRepositoryContract::class, ContentVersionRepository::class);
        $this->app->bind(HookScoreRepositoryContract::class, HookScoreRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(HookScoringService::class);
        $this->app->singleton(HookGenerationService::class);
        $this->app->singleton(GenerateHooksAction::class);
    }
}
