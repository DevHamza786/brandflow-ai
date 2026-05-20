<?php

declare(strict_types=1);

namespace App\Domains\Brand\Providers;

use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Contracts\BrandMemoryEnrichmentServiceContract;
use App\Domains\Brand\Contracts\BrandMemoryQueryServiceContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;
use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Contracts\WritingStyleExtractionServiceContract;
use App\Domains\Brand\Repositories\BrandProfileRepository;
use App\Domains\Brand\Repositories\WritingSampleRepository;
use App\Domains\Brand\Services\BrandMemoryContextService;
use App\Domains\Brand\Services\BrandMemoryEnrichmentService;
use App\Domains\Brand\Services\BrandMemoryOrchestrationService;
use App\Domains\Brand\Services\BrandMemoryQueryService;
use App\Domains\Brand\Services\BrandMemorySelectionService;
use App\Domains\Brand\Services\MemoryRetrievalService;
use App\Domains\Brand\Services\WritingStyleExtractionService;
use App\Domains\Brand\Support\BrandMemoryNormalizer;
use App\Domains\Brand\Support\BrandMemoryPromptInjector;
use App\Domains\Brand\Support\BrandMemorySerializer;
use App\Domains\Brand\Support\HookBrandMemoryPromptComposer;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class BrandServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Brand';
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(BrandMemoryNormalizer::class);
        $this->app->singleton(BrandMemorySerializer::class);
        $this->app->singleton(BrandMemoryPromptInjector::class);
        $this->app->singleton(HookBrandMemoryPromptComposer::class);
        $this->app->singleton(BrandMemorySelectionService::class);

        $this->app->bind(BrandProfileRepositoryContract::class, BrandProfileRepository::class);
        $this->app->bind(WritingSampleRepositoryContract::class, WritingSampleRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(WritingStyleExtractionServiceContract::class, WritingStyleExtractionService::class);
        $this->app->singleton(BrandMemoryEnrichmentServiceContract::class, BrandMemoryEnrichmentService::class);
        $this->app->singleton(BrandMemoryQueryServiceContract::class, BrandMemoryQueryService::class);
        $this->app->singleton(BrandMemoryContextServiceContract::class, BrandMemoryContextService::class);
        $this->app->singleton(BrandMemoryOrchestrationService::class);
        $this->app->singleton(MemoryRetrievalServiceContract::class, MemoryRetrievalService::class);
    }
}
