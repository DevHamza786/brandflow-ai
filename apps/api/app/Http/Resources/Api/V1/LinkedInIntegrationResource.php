<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Never exposes access_token or refresh_token.
 *
 * @mixin LinkedInIntegrationDto
 */
final class LinkedInIntegrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var LinkedInIntegrationDto $integration */
        $integration = $this->resource;

        return [
            'id' => $integration->id,
            'workspace_id' => $integration->workspaceId,
            'provider' => $integration->provider->value,
            'linkedin_member_id' => $integration->linkedinMemberId,
            'status' => $integration->status->value,
            'scopes' => $integration->scopes,
            'metadata' => $integration->metadata,
            'token_expires_at' => $integration->tokenExpiresAt?->toIso8601String(),
            'connected_at' => $integration->connectedAt?->toIso8601String(),
            'last_synced_at' => $integration->lastSyncedAt?->toIso8601String(),
            'last_error' => $integration->lastError,
            'refresh_attempts' => $integration->refreshAttempts,
            'has_access_token' => $integration->hasAccessToken,
            'has_refresh_token' => $integration->hasRefreshToken,
            'is_token_expired' => $integration->isTokenExpired,
            'created_at' => $integration->createdAt?->toIso8601String(),
            'updated_at' => $integration->updatedAt?->toIso8601String(),
        ];
    }
}
