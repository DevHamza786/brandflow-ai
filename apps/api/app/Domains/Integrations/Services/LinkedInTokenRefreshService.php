<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Data\UpdateLinkedInIntegrationTokensDto;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshed;
use App\Domains\Integrations\Events\LinkedInIntegrationRefreshFailed;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use App\Domains\Integrations\Support\IntegrationLogger;
use Illuminate\Support\Facades\Event;

/**
 * Refresh expiring tokens — scheduler-safe, idempotent per integration row.
 */
final class LinkedInTokenRefreshService
{
    public function __construct(
        private readonly LinkedInIntegrationRepositoryContract $integrations,
        private readonly OAuthProviderContract $provider,
        private readonly IntegrationLogger $logger,
    ) {
    }

    public function refresh(string $workspaceId, string $integrationId, ?string $traceId = null): LinkedInIntegrationDto
    {
        $traceId ??= $this->logger->traceId();
        $integration = $this->integrations->findById($workspaceId, $integrationId);

        if ($integration === null) {
            throw new LinkedInOAuthException('Integration not found.', [
                'workspace_id' => $workspaceId,
                'integration_id' => $integrationId,
            ]);
        }

        if (! $integration->status->allowsRefresh()) {
            throw new LinkedInOAuthException('Integration cannot be refreshed in current status.', [
                'status' => $integration->status->value,
            ]);
        }

        $refreshToken = $this->integrations->getDecryptedRefreshToken($workspaceId, $integrationId);
        if ($refreshToken === null || $refreshToken === '') {
            $this->integrations->updateStatus(
                $workspaceId,
                $integrationId,
                IntegrationStatus::Expired,
                'Missing refresh token',
            );

            throw new LinkedInOAuthException('No refresh token available.');
        }

        $maxAttempts = (int) config('integrations.max_refresh_attempts', 5);
        if ($integration->refreshAttempts >= $maxAttempts) {
            $this->integrations->updateStatus(
                $workspaceId,
                $integrationId,
                IntegrationStatus::Error,
                'Max refresh attempts exceeded',
            );

            throw new LinkedInOAuthException('Max token refresh attempts exceeded.');
        }

        $this->logger->info('oauth.refresh.start', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
            'integration_id' => $integrationId,
        ]);

        try {
            $bundle = $this->provider->refreshAccessToken($refreshToken);

            $updated = $this->integrations->updateTokens(
                $workspaceId,
                $integrationId,
                new UpdateLinkedInIntegrationTokensDto(
                    accessToken: $bundle->accessToken,
                    refreshToken: $bundle->refreshToken,
                    tokenExpiresAt: $bundle->expiresAt,
                    scopes: $bundle->scopes !== [] ? $bundle->scopes : null,
                    status: IntegrationStatus::Connected,
                    resetRefreshAttempts: true,
                ),
            );

            Event::dispatch(new LinkedInIntegrationRefreshed($updated, $traceId));

            $this->logger->info('oauth.refresh.success', [
                'trace_id' => $traceId,
                'integration_id' => $integrationId,
            ]);

            return $updated;
        } catch (\Throwable $e) {
            $this->integrations->incrementRefreshAttempts($workspaceId, $integrationId);
            $this->integrations->updateStatus(
                $workspaceId,
                $integrationId,
                IntegrationStatus::Error,
                $e->getMessage(),
            );

            Event::dispatch(new LinkedInIntegrationRefreshFailed(
                $workspaceId,
                $integrationId,
                $e->getMessage(),
                $traceId,
            ));

            $this->logger->error('oauth.refresh.failed', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'integration_id' => $integrationId,
            ], $e);

            throw $e instanceof LinkedInOAuthException
                ? $e
                : new LinkedInOAuthException($e->getMessage(), [], 0, $e);
        }
    }

    public function refreshIfExpiring(LinkedInIntegrationDto $integration, ?string $traceId = null): LinkedInIntegrationDto
    {
        if (! $integration->isTokenExpired && ! $this->expiresWithinLead($integration)) {
            return $integration;
        }

        return $this->refresh($integration->workspaceId, $integration->id, $traceId);
    }

    private function expiresWithinLead(LinkedInIntegrationDto $integration): bool
    {
        if ($integration->tokenExpiresAt === null) {
            return false;
        }

        $lead = (int) config('integrations.token_refresh_lead_seconds', 3600);

        return $integration->tokenExpiresAt->lte(now()->addSeconds($lead));
    }
}
