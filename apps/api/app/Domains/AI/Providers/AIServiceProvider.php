<?php

declare(strict_types=1);

namespace App\Domains\AI\Providers;

use App\Domains\AI\Adapters\NullLlmGateway;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\Shared\Providers\DomainServiceProvider;

final class AIServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'AI';
    }

    protected function registerServices(): void
    {
        $this->app->singleton(LlmGateway::class, NullLlmGateway::class);

        // $this->app->singleton(PromptTemplateRegistryContract::class, PromptTemplateRegistry::class);
    }
}
