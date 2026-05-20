<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Data\CreateLinkedInIntegrationDto;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Data\OAuthTokenBundleDto;
use App\Domains\Integrations\Data\UpdateLinkedInIntegrationTokensDto;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Support\IntegrationLogger;
use App\Domains\Shared\Services\WorkspaceBootstrapService;

/**
 * Persists OAuth tokens to workspace-scoped integration rows (upsert by member id).
 */
final class LinkedInIntegrationLinkService
{
    public function __construct(
        private readonly LinkedInIntegrationRepositoryContract $integrations,
        private readonly OAuthProviderContract $provider,
        private readonly IntegrationLogger $logger,
        private readonly WorkspaceBootstrapService $workspaceBootstrap,
    ) {
    }

    public function linkFromTokenBundle(
        string $workspaceId,
        OAuthTokenBundleDto $bundle,
        string $traceId,
    ): LinkedInIntegrationDto {
        $this->workspaceBootstrap->ensureLocalWorkspaceRecord($workspaceId);

        $memberId = $this->provider->resolveMemberId($bundle->accessToken);

        $metadata = [
            'provider_raw_keys' => array_keys($bundle->raw),
            'linked_trace_id' => $traceId,
        ];

        $existing = $this->integrations->findByMemberId($workspaceId, $memberId);

        if ($existing !== null) {
            $this->logger->info('integration.link.update', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'integration_id' => $existing->id,
                'linkedin_member_id' => $memberId,
            ]);

            return $this->integrations->updateTokens(
                $workspaceId,
                $existing->id,
                new UpdateLinkedInIntegrationTokensDto(
                    accessToken: $bundle->accessToken,
                    refreshToken: $bundle->refreshToken,
                    tokenExpiresAt: $bundle->expiresAt,
                    scopes: $bundle->scopes,
                    metadata: $metadata,
                    status: IntegrationStatus::Connected,
                    linkedinMemberId: $memberId,
                    resetRefreshAttempts: true,
                ),
            );
        }

        $this->logger->info('integration.link.create', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
            'linkedin_member_id' => $memberId,
        ]);

        return $this->integrations->create(new CreateLinkedInIntegrationDto(
            workspaceId: $workspaceId,
            accessToken: $bundle->accessToken,
            refreshToken: $bundle->refreshToken,
            tokenExpiresAt: $bundle->expiresAt,
            linkedinMemberId: $memberId,
            scopes: $bundle->scopes,
            metadata: $metadata,
            status: IntegrationStatus::Connected,
        ));
    }
}
