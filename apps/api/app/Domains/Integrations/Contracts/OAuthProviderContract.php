<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Contracts;

use App\Domains\Integrations\Data\OAuthTokenBundleDto;
use App\Domains\Integrations\Enums\IntegrationProvider;

/**
 * Provider abstraction for multi-platform OAuth (LinkedIn first).
 */
interface OAuthProviderContract
{
    public function provider(): IntegrationProvider;

    public function authorizationUrl(string $state, array $scopes = []): string;

    public function exchangeAuthorizationCode(string $code, string $redirectUri): OAuthTokenBundleDto;

    public function refreshAccessToken(string $refreshToken): OAuthTokenBundleDto;

    public function resolveMemberId(string $accessToken): string;
}
