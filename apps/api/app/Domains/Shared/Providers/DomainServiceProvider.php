<?php

declare(strict_types=1);

namespace App\Domains\Shared\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for bounded contexts.
 */
abstract class DomainServiceProvider extends ServiceProvider
{
    abstract protected function domainName(): string;

    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
    }

    public function boot(): void
    {
        //
    }

    protected function registerRepositories(): void
    {
        //
    }

    protected function registerServices(): void
    {
        //
    }
}
