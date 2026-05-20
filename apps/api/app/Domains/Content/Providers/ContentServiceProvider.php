<?php

declare(strict_types=1);

namespace App\Domains\Content\Providers;

use App\Domains\Agents\Events\AgentRunFailed;
use App\Domains\Agents\Events\AgentRunStarted;
use App\Domains\Content\Actions\GenerateHooksAction;
use App\Domains\Content\Actions\PersistHookGeneratedOutputAction;
use App\Domains\Content\Actions\ReserveHookGeneratedOutputAction;
use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Contracts\HookScoreRepositoryContract;
use App\Domains\Content\Events\HookScored;
use App\Domains\Content\Listeners\FinalizeHookGenerationWorkflow;
use App\Domains\Content\Listeners\LogHookScoredAnalytics;
use App\Domains\Content\Listeners\SyncHookGeneratedOutputLifecycle;
use App\Domains\Content\Listeners\TrackHookGenerationWorkflowProgress;
use App\Domains\Content\Repositories\ContentVersionRepository;
use App\Domains\Content\Repositories\HookScoreRepository;
use App\Domains\Agents\Agents\HookAgent\Services\HookAgentMemoryEnrichmentService;
use App\Domains\Agents\Agents\HookAgent\Support\HookBannedPhraseFilter;
use App\Domains\Agents\Agents\HookAgent\Support\HookPersonalizationLogger;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;
use App\Domains\Content\Services\HookGenerationService;
use App\Domains\Content\Services\HookScoringService;
use App\Domains\Content\Services\HookWorkflowService;
use App\Domains\Content\Support\HookGeneratedOutputMapper;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

final class ContentServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Content';
    }

    public function boot(): void
    {
        // Prevent implicit Eloquent binding on *Id suffix params (wrong column / no workspace scope).
        Route::bind('versionId', static fn (string $value): string => $value);
        Route::bind('agentRunId', static fn (string $value): string => $value);

        $finalize = app(FinalizeHookGenerationWorkflow::class);
        $generatedOutputLifecycle = app(SyncHookGeneratedOutputLifecycle::class);

        Event::listen(HookScored::class, [$finalize, 'handleHookScored']);
        Event::listen(AgentRunFailed::class, [$finalize, 'handleAgentRunFailed']);
        Event::listen(AgentRunFailed::class, [$generatedOutputLifecycle, 'handleAgentRunFailed']);
        Event::listen(AgentRunStarted::class, [TrackHookGenerationWorkflowProgress::class, 'handle']);
        Event::listen(AgentRunStarted::class, [$generatedOutputLifecycle, 'handleAgentRunStarted']);
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
        $this->app->singleton(HookAgentMemoryEnrichmentService::class);
        $this->app->singleton(HookPersonalizationLogger::class);
        $this->app->singleton(HookBannedPhraseFilter::class);
        $this->app->singleton(HookGeneratedOutputMapper::class);
        $this->app->singleton(HookGeneratedOutputPersistenceService::class);
        $this->app->singleton(HookWorkflowService::class);
        $this->app->singleton(GenerateHooksAction::class);
        $this->app->singleton(PersistHookGeneratedOutputAction::class);
        $this->app->singleton(ReserveHookGeneratedOutputAction::class);
        $this->app->singleton(FinalizeHookGenerationWorkflow::class);
        $this->app->singleton(TrackHookGenerationWorkflowProgress::class);
    }
}
