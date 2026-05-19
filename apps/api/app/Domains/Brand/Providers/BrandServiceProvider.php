<?php

declare(strict_types=1);

namespace App\Domains\Brand\Providers;

use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;
use App\Domains\Brand\Services\MemoryRetrievalService;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class BrandServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Brand';
    }

    protected function registerServices(): void
    {
        $this->app->singleton(MemoryRetrievalServiceContract::class, MemoryRetrievalService::class);
    }
}
