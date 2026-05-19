<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

use App\Domains\AI\Exceptions\ProviderAuthenticationException;
use App\Domains\AI\Exceptions\ProviderException;
use App\Domains\AI\Exceptions\ProviderRateLimitException;
use App\Domains\AI\Exceptions\ProviderTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Shared HTTP helpers for vendor adapters (no business logic).
 */
final class ProviderHttpClient
{
    public function baseRequest(string $baseUrl, string $apiKey, int $timeout): PendingRequest
    {
        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->timeout($timeout)
            ->acceptJson()
            ->withToken($apiKey);
    }

    public function throwIfFailed(Response $response, string $provider): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();
        $body = $response->json() ?? ['raw' => $response->body()];
        $message = (string) ($body['error']['message'] ?? $body['error']['status'] ?? $response->body());

        $context = [
            'provider' => $provider,
            'status' => $status,
            'body' => $body,
        ];

        match (true) {
            $status === 401, $status === 403 => throw new ProviderAuthenticationException(
                "[{$provider}] Authentication failed: {$message}",
                $context
            ),
            $status === 429 => throw new ProviderRateLimitException(
                "[{$provider}] Rate limited: {$message}",
                retryAfterSeconds: (int) $response->header('Retry-After'),
                context: $context
            ),
            $status >= 500 => throw new ProviderException(
                "[{$provider}] Server error ({$status}): {$message}",
                $context,
                $status
            ),
            default => throw new ProviderException(
                "[{$provider}] Request failed ({$status}): {$message}",
                $context,
                $status
            ),
        };
    }

    public function wrapConnectionException(ConnectionException $e, string $provider): ProviderTimeoutException
    {
        return new ProviderTimeoutException(
            "[{$provider}] Connection timeout: {$e->getMessage()}",
            ['provider' => $provider],
            previous: $e
        );
    }
}
