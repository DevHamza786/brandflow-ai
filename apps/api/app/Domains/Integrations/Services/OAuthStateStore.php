<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Data\OAuthStateDto;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Redis-backed OAuth CSRF state (workspace-bound).
 */
final class OAuthStateStore
{
    private const CACHE_PREFIX = 'pbos:oauth:state:';

    public function create(
        string $workspaceId,
        IntegrationProvider $provider,
        string $redirectAfter,
        array $metadata = [],
    ): OAuthStateDto {
        $ttl = (int) config('integrations.oauth_state_ttl_seconds', 600);
        $nonce = Str::random(48);
        $expiresAt = CarbonImmutable::now()->addSeconds($ttl);

        $state = new OAuthStateDto(
            nonce: $nonce,
            workspaceId: $workspaceId,
            provider: $provider,
            redirectAfter: $redirectAfter,
            expiresAt: $expiresAt,
            metadata: $metadata,
        );

        Cache::put(
            self::CACHE_PREFIX.$nonce,
            [
                'workspace_id' => $workspaceId,
                'provider' => $provider->value,
                'redirect_after' => $redirectAfter,
                'expires_at' => $expiresAt->toIso8601String(),
                'metadata' => $metadata,
            ],
            $expiresAt,
        );

        return $state;
    }

    public function consume(string $nonce): OAuthStateDto
    {
        $key = self::CACHE_PREFIX.$nonce;
        /** @var array<string, mixed>|null $payload */
        $payload = Cache::pull($key);

        if ($payload === null || ! isset($payload['workspace_id'], $payload['provider'])) {
            throw new LinkedInOAuthException(
                'Invalid or expired OAuth state.',
                ['nonce_prefix' => substr($nonce, 0, 8)],
            );
        }

        return new OAuthStateDto(
            nonce: $nonce,
            workspaceId: (string) $payload['workspace_id'],
            provider: IntegrationProvider::fromString((string) $payload['provider']),
            redirectAfter: (string) ($payload['redirect_after'] ?? config('integrations.default_success_redirect')),
            expiresAt: CarbonImmutable::parse((string) ($payload['expires_at'] ?? now()->toIso8601String())),
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
        );
    }

    public function encodeStateParameter(OAuthStateDto $state): string
    {
        return rtrim(strtr(base64_encode(json_encode([
            'n' => $state->nonce,
            'w' => $state->workspaceId,
        ], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    }

    /**
     * @return array{nonce: string, workspace_id: string}
     */
    public function decodeStateParameter(string $stateParam): array
    {
        $padded = strtr($stateParam, '-_', '+/');
        $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
        $decoded = json_decode(base64_decode($padded, true) ?: '', true);

        if (! is_array($decoded) || ! isset($decoded['n'], $decoded['w'])) {
            throw new LinkedInOAuthException('Malformed OAuth state parameter.');
        }

        return [
            'nonce' => (string) $decoded['n'],
            'workspace_id' => (string) $decoded['w'],
        ];
    }
}
