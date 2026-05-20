<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Integrations\Actions\StartLinkedInOAuthAction;
use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use App\Domains\Integrations\Services\LinkedInOAuthService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Resources\Api\V1\LinkedInIntegrationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * LinkedIn OAuth — thin HTTP layer; business logic in domain services/actions.
 */
final class LinkedInOAuthController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly StartLinkedInOAuthAction $startOAuth,
        private readonly LinkedInIntegrationRepositoryContract $integrations,
        private readonly LinkedInOAuthService $oauthService,
    ) {
    }

    /**
     * GET /api/v1/integrations/linkedin/connect
     */
    public function connect(Request $request): JsonResponse|RedirectResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $redirectAfter = $request->query('redirect_after');
        $redirectAfter = is_string($redirectAfter) && $redirectAfter !== '' ? $redirectAfter : null;

        try {
            $payload = $this->startOAuth->execute($workspaceId, $redirectAfter);
        } catch (LinkedInOAuthException $e) {
            return $this->problem(
                status: 422,
                type: 'https://pbos.dev/problems/linkedin-oauth-config',
                title: 'LinkedIn OAuth Not Configured',
                detail: $e->getMessage(),
                context: $e->context,
            );
        }

        if ($request->boolean('redirect')) {
            return redirect()->away($payload['authorization_url']);
        }

        return $this->success($payload);
    }

    /**
     * GET /api/v1/integrations/linkedin
     */
    public function index(Request $request): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $list = $this->integrations->listByWorkspace($workspaceId);

        return $this->success([
            'integrations' => LinkedInIntegrationResource::collection($list)->resolve(),
        ]);
    }

    /**
     * GET /api/v1/integrations/linkedin/{integrationId}
     */
    public function show(Request $request, string $integrationId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $integration = $this->integrations->findById($workspaceId, $integrationId);

        if ($integration === null) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/integration-not-found',
                title: 'Integration Not Found',
                detail: 'LinkedIn integration not found for this workspace.',
            );
        }

        return $this->success(LinkedInIntegrationResource::make($integration));
    }

    /**
     * DELETE /api/v1/integrations/linkedin/{integrationId}
     */
    public function destroy(Request $request, string $integrationId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $integration = $this->integrations->findById($workspaceId, $integrationId);

        if ($integration === null) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/integration-not-found',
                title: 'Integration Not Found',
                detail: 'LinkedIn integration not found for this workspace.',
            );
        }

        $updated = $this->oauthService->disconnect($workspaceId, $integrationId);

        return $this->success(LinkedInIntegrationResource::make($updated));
    }
}
