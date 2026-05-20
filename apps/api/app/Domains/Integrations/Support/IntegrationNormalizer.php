<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Support;

use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Models\LinkedInIntegration;
use Carbon\CarbonImmutable;

final class IntegrationNormalizer
{
    public function normalize(LinkedInIntegration $model): LinkedInIntegrationDto
    {
        $expiresAt = $model->token_expires_at;
        $isExpired = $expiresAt !== null && $expiresAt->isPast();

        return new LinkedInIntegrationDto(
            id: (string) $model->id,
            workspaceId: (string) $model->workspace_id,
            provider: IntegrationProvider::fromString((string) $model->provider),
            linkedinMemberId: $model->linkedin_member_id,
            status: IntegrationStatus::fromString((string) $model->status),
            scopes: is_array($model->scopes) ? array_values($model->scopes) : [],
            metadata: is_array($model->metadata) ? $model->metadata : [],
            tokenExpiresAt: $expiresAt,
            connectedAt: $model->connected_at,
            lastSyncedAt: $model->last_synced_at,
            lastError: $model->last_error,
            refreshAttempts: (int) $model->refresh_attempts,
            hasAccessToken: $model->access_token !== null && $model->access_token !== '',
            hasRefreshToken: $model->refresh_token !== null && $model->refresh_token !== '',
            isTokenExpired: $isExpired,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    /**
     * @param  list<string>  $scopeString
     * @return list<string>
     */
    public function parseScopes(string|array|null $scopeString): array
    {
        if (is_array($scopeString)) {
            return array_values(array_filter($scopeString));
        }

        if (! is_string($scopeString) || trim($scopeString) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(' ', $scopeString))));
    }

    public function expiresAtFromSeconds(?int $expiresIn): ?CarbonImmutable
    {
        if ($expiresIn === null || $expiresIn <= 0) {
            return null;
        }

        return CarbonImmutable::now()->addSeconds($expiresIn);
    }
}
