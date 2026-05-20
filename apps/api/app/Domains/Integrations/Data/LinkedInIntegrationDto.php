<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Data;

use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * API-safe integration view — never exposes raw tokens.
 */
final class LinkedInIntegrationDto extends DataTransferObject
{
    /**
     * @param  list<string>  $scopes
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly IntegrationProvider $provider,
        public readonly ?string $linkedinMemberId,
        public readonly IntegrationStatus $status,
        public readonly array $scopes,
        public readonly array $metadata,
        public readonly ?CarbonInterface $tokenExpiresAt,
        public readonly ?CarbonInterface $connectedAt,
        public readonly ?CarbonInterface $lastSyncedAt,
        public readonly ?string $lastError,
        public readonly int $refreshAttempts,
        public readonly bool $hasAccessToken,
        public readonly bool $hasRefreshToken,
        public readonly bool $isTokenExpired,
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $updatedAt = null,
    ) {
    }
}
