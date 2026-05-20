<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Data\OAuthTokenBundleDto;
use App\Domains\Integrations\Support\IntegrationLogger;

/**
 * Authorization code → access token exchange.
 */
final class LinkedInTokenExchangeService
{
    public function __construct(
        private readonly OAuthProviderContract $provider,
        private readonly IntegrationLogger $logger,
    ) {
    }

    public function exchange(string $code, string $traceId): OAuthTokenBundleDto
    {
        $redirectUri = (string) config('integrations.linkedin.redirect_uri');

        $this->logger->info('oauth.exchange.start', [
            'trace_id' => $traceId,
            'provider' => $this->provider->provider()->value,
        ]);

        $bundle = $this->provider->exchangeAuthorizationCode($code, $redirectUri);

        $this->logger->info('oauth.exchange.success', [
            'trace_id' => $traceId,
            'expires_at' => $bundle->expiresAt?->toIso8601String(),
            'scope_count' => count($bundle->scopes),
        ]);

        return $bundle;
    }
}
