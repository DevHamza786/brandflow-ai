<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Providers\LinkedIn;

use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Data\OAuthTokenBundleDto;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use App\Domains\Integrations\Support\IntegrationNormalizer;
use Illuminate\Support\Facades\Http;

final class LinkedInOAuthProvider implements OAuthProviderContract
{
    public function __construct(
        private readonly IntegrationNormalizer $normalizer,
    ) {
    }

    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::LinkedIn;
    }

    public function authorizationUrl(string $state, array $scopes = []): string
    {
        $clientId = $this->clientId();
        $redirectUri = $this->redirectUri();
        $scopes = $scopes !== [] ? $scopes : $this->defaultScopes();

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => implode(' ', $scopes),
        ]);

        return rtrim((string) config('integrations.linkedin.authorize_url'), '?').'?'.$query;
    }

    public function exchangeAuthorizationCode(string $code, string $redirectUri): OAuthTokenBundleDto
    {
        $response = Http::asForm()
            ->timeout($this->timeout())
            ->post((string) config('integrations.linkedin.token_url'), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
            ]);

        if (! $response->successful()) {
            throw new LinkedInOAuthException(
                'LinkedIn token exchange failed.',
                [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ],
            );
        }

        return $this->mapTokenResponse($response->json());
    }

    public function refreshAccessToken(string $refreshToken): OAuthTokenBundleDto
    {
        $response = Http::asForm()
            ->timeout($this->timeout())
            ->post((string) config('integrations.linkedin.token_url'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
            ]);

        if (! $response->successful()) {
            throw new LinkedInOAuthException(
                'LinkedIn token refresh failed.',
                [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ],
            );
        }

        $bundle = $this->mapTokenResponse($response->json());
        if ($bundle->refreshToken === null) {
            return new OAuthTokenBundleDto(
                accessToken: $bundle->accessToken,
                refreshToken: $refreshToken,
                expiresAt: $bundle->expiresAt,
                scopes: $bundle->scopes,
                raw: $bundle->raw,
            );
        }

        return $bundle;
    }

    public function resolveMemberId(string $accessToken): string
    {
        $response = Http::withToken($accessToken)
            ->timeout($this->timeout())
            ->get((string) config('integrations.linkedin.userinfo_url'));

        if (! $response->successful()) {
            throw new LinkedInOAuthException(
                'Failed to resolve LinkedIn member id.',
                ['status' => $response->status(), 'body' => $response->json() ?? $response->body()],
            );
        }

        $data = $response->json();
        $sub = is_array($data) ? ($data['sub'] ?? $data['id'] ?? null) : null;

        if (! is_string($sub) || $sub === '') {
            throw new LinkedInOAuthException('LinkedIn userinfo missing subject id.', ['response' => $data]);
        }

        return $sub;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function mapTokenResponse(?array $payload): OAuthTokenBundleDto
    {
        if ($payload === null || ! isset($payload['access_token'])) {
            throw new LinkedInOAuthException('Invalid token response from LinkedIn.', ['payload' => $payload]);
        }

        $expiresIn = isset($payload['expires_in']) ? (int) $payload['expires_in'] : null;

        return new OAuthTokenBundleDto(
            accessToken: (string) $payload['access_token'],
            refreshToken: isset($payload['refresh_token']) ? (string) $payload['refresh_token'] : null,
            expiresAt: $this->normalizer->expiresAtFromSeconds($expiresIn),
            scopes: $this->normalizer->parseScopes($payload['scope'] ?? []),
            raw: $payload,
        );
    }

    private function clientId(): string
    {
        $id = config('integrations.linkedin.client_id');
        if (! is_string($id) || $id === '') {
            throw new LinkedInOAuthException('LINKEDIN_CLIENT_ID is not configured.');
        }

        return $id;
    }

    private function clientSecret(): string
    {
        $secret = config('integrations.linkedin.client_secret');
        if (! is_string($secret) || $secret === '') {
            throw new LinkedInOAuthException('LINKEDIN_CLIENT_SECRET is not configured.');
        }

        return $secret;
    }

    private function redirectUri(): string
    {
        return (string) config('integrations.linkedin.redirect_uri');
    }

    /**
     * @return list<string>
     */
    private function defaultScopes(): array
    {
        /** @var list<string> $scopes */
        $scopes = config('integrations.linkedin.scopes', []);

        return $scopes;
    }

    private function timeout(): int
    {
        return (int) config('integrations.linkedin.http_timeout_seconds', 30);
    }
}
