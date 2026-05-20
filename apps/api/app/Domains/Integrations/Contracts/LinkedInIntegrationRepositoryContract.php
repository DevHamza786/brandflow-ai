<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Contracts;

use App\Domains\Integrations\Data\CreateLinkedInIntegrationDto;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Data\UpdateLinkedInIntegrationTokensDto;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface LinkedInIntegrationRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?LinkedInIntegrationDto;

    public function findByMemberId(
        string $workspaceId,
        string $linkedinMemberId,
    ): ?LinkedInIntegrationDto;

    /**
     * @return list<LinkedInIntegrationDto>
     */
    public function listByWorkspace(string $workspaceId, int $limit = 20): array;

    /**
     * @return list<LinkedInIntegrationDto>
     */
    public function listExpiringBefore(\DateTimeInterface $before, int $limit = 50): array;

    public function create(CreateLinkedInIntegrationDto $dto): LinkedInIntegrationDto;

    public function updateTokens(
        string $workspaceId,
        string $id,
        UpdateLinkedInIntegrationTokensDto $dto,
    ): LinkedInIntegrationDto;

    public function updateStatus(
        string $workspaceId,
        string $id,
        IntegrationStatus $status,
        ?string $lastError = null,
    ): LinkedInIntegrationDto;

    public function markSynced(string $workspaceId, string $id): LinkedInIntegrationDto;

    public function incrementRefreshAttempts(string $workspaceId, string $id): LinkedInIntegrationDto;

    /**
     * Decrypted access token for internal services only — never expose via API.
     */
    public function getDecryptedAccessToken(string $workspaceId, string $id): ?string;

    /**
     * Decrypted refresh token for internal services only.
     */
    public function getDecryptedRefreshToken(string $workspaceId, string $id): ?string;
}
