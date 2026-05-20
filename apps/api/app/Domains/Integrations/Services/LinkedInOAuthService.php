<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\OAuthProviderContract;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Events\LinkedInIntegrationConnected;
use App\Domains\Integrations\Events\LinkedInIntegrationFailed;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use App\Domains\Integrations\Support\IntegrationLogger;
use Illuminate\Support\Facades\Event;

/**
 * OAuth orchestration — connect, callback, disconnect (no HTTP knowledge).
 */
final class LinkedInOAuthService
{
    public function __construct(
        private readonly OAuthStateStore $stateStore,
        private readonly OAuthProviderContract $provider,
        private readonly LinkedInTokenExchangeService $tokenExchange,
        private readonly LinkedInIntegrationLinkService $linkService,
        private readonly IntegrationStatusService $statusService,
        private readonly IntegrationLogger $logger,
    ) {
    }

    /**
     * @return array{authorization_url: string, state: string, expires_at: string}
     */
    public function beginConnect(
        string $workspaceId,
        ?string $redirectAfter = null,
    ): array {
        $traceId = $this->logger->traceId();
        $redirectAfter ??= (string) config('integrations.default_success_redirect');

        $oauthState = $this->stateStore->create(
            workspaceId: $workspaceId,
            provider: IntegrationProvider::LinkedIn,
            redirectAfter: $redirectAfter,
            metadata: ['trace_id' => $traceId],
        );

        $stateParam = $this->stateStore->encodeStateParameter($oauthState);
        $authorizationUrl = $this->provider->authorizationUrl($stateParam);

        $this->logger->info('oauth.connect.start', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
        ]);

        return [
            'authorization_url' => $authorizationUrl,
            'state' => $stateParam,
            'expires_at' => $oauthState->expiresAt->toIso8601String(),
        ];
    }

    /**
     * @return array{integration: LinkedInIntegrationDto, redirect_url: string}
     */
    public function completeConnect(string $stateParam, string $code, ?string $traceId = null): array
    {
        $traceId ??= $this->logger->traceId();

        try {
            $decoded = $this->stateStore->decodeStateParameter($stateParam);
            $oauthState = $this->stateStore->consume($decoded['nonce']);
            $workspaceId = $oauthState->workspaceId;

            $bundle = $this->tokenExchange->exchange($code, $traceId);
            $integration = $this->linkService->linkFromTokenBundle($workspaceId, $bundle, $traceId);
            $integration = $this->statusService->markConnected($workspaceId, $integration->id);

            Event::dispatch(new LinkedInIntegrationConnected($integration, $traceId));

            $this->logger->info('oauth.callback.success', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'integration_id' => $integration->id,
            ]);

            return [
                'integration' => $integration,
                'redirect_url' => $oauthState->redirectAfter,
            ];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Event::dispatch(new LinkedInIntegrationFailed(
                workspaceId: $this->resolveWorkspaceFromState($stateParam),
                message: $message,
                traceId: $traceId,
            ));

            $this->logger->error('oauth.callback.failed', ['trace_id' => $traceId], $e);

            throw $e instanceof LinkedInOAuthException
                ? $e
                : new LinkedInOAuthException($message, [], 0, $e);
        }
    }

    public function disconnect(string $workspaceId, string $integrationId, ?string $traceId = null): LinkedInIntegrationDto
    {
        $traceId ??= $this->logger->traceId();

        $updated = $this->statusService->markDisconnected($workspaceId, $integrationId);

        $this->logger->info('integration.disconnected', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
            'integration_id' => $integrationId,
        ]);

        return $updated;
    }

    private function resolveWorkspaceFromState(string $stateParam): string
    {
        try {
            return $this->stateStore->decodeStateParameter($stateParam)['workspace_id'];
        } catch (\Throwable) {
            return 'unknown';
        }
    }
}
