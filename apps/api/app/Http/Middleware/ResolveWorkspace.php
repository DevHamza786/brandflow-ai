<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Shared\Services\WorkspaceBootstrapService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves workspace context from X-Workspace-Id header (auth policies extend later).
 * In local dev, upserts a minimal `workspaces` row when missing (FK safety for brand, content, etc.).
 */
final class ResolveWorkspace
{
    public const ATTRIBUTE = 'workspace_id';

    public function __construct(
        private readonly WorkspaceBootstrapService $workspaceBootstrap,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->header('X-Workspace-Id')
            ?? $request->route('workspaceId');

        if (! is_string($workspaceId) || $workspaceId === '') {
            return response()->json([
                'type' => 'https://pbos.dev/problems/missing-workspace',
                'title' => 'Workspace Required',
                'status' => 400,
                'detail' => 'Provide a valid X-Workspace-Id header.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        if (! $this->isValidUuid($workspaceId)) {
            return response()->json([
                'type' => 'https://pbos.dev/problems/invalid-workspace',
                'title' => 'Invalid Workspace',
                'status' => 400,
                'detail' => 'X-Workspace-Id must be a valid UUID.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        $this->workspaceBootstrap->ensureLocalWorkspaceRecord($workspaceId);

        $request->attributes->set(self::ATTRIBUTE, $workspaceId);

        return $next($request);
    }

    private function isValidUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}
