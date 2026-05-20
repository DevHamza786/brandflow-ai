<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Providers;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Events\LinkedInIntegrationConnected;
use App\Domains\Integrations\Events\LinkedInIntegrationFailed;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshFailed;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshed;
use App\Domains\Integrations\Listeners\LogIntegrationEventListener;
use App\Domains\Integrations\Providers\LinkedIn\LinkedInOAuthProvider;
use App\Domains\Integrations\Repositories\LinkedInIntegrationRepository;
use App\Domains\Integrations\Support\IntegrationCredentialVault;
use App\Domains\Integrations\Support\IntegrationLogger;
use App\Domains\Integrations\Support\IntegrationNormalizer;
use App\Domains\Integrations\Services\IntegrationStatusService;
use App\Domains\Integrations\Services\LinkedInIntegrationLinkService;
use App\Domains\Integrations\Services\LinkedInOAuthService;
use App\Domains\Integrations\Services\LinkedInTokenExchangeService;
use App\Domains\Integrations\Contracts\SocialPublishingProviderContract;
use App\Domains\Integrations\Services\LinkedInSocialPublisher;
use App\Domains\Integrations\Services\OAuthStateStore;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\Event;

final class IntegrationsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Integrations';
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(IntegrationNormalizer::class);
        $this->app->singleton(IntegrationLogger::class);
        $this->app->singleton(IntegrationCredentialVault::class);
        $this->app->singleton(OAuthStateStore::class);

        $this->app->bind(LinkedInIntegrationRepositoryContract::class, LinkedInIntegrationRepository::class);
        $this->app->bind(OAuthProviderContract::class, LinkedInOAuthProvider::class);
        $this->app->bind(SocialPublishingProviderContract::class, LinkedInSocialPublisher::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(LinkedInTokenExchangeService::class);
        $this->app->singleton(LinkedInTokenRefreshService::class);
        $this->app->singleton(LinkedInIntegrationLinkService::class);
        $this->app->singleton(IntegrationStatusService::class);
        $this->app->singleton(LinkedInOAuthService::class);
    }

    public function boot(): void
    {
        $listener = LogIntegrationEventListener::class;

        Event::listen(LinkedInIntegrationConnected::class, [$listener, 'handleConnected']);
        Event::listen(LinkedInIntegrationFailed::class, [$listener, 'handleFailed']);
        Event::listen(LinkedInIntegrationRefreshed::class, [$listener, 'handleRefreshed']);
        Event::listen(LinkedInIntegrationRefreshFailed::class, [$listener, 'handleRefreshFailed']);
    }
}
