<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Services\LinkedInOAuthService;

final class StartLinkedInOAuthAction
{
    public function __construct(
        private readonly LinkedInOAuthService $oauth,
    ) {
    }

    /**
     * @return array{authorization_url: string, state: string, expires_at: string}
     */
    public function execute(string $workspaceId, ?string $redirectAfter = null): array
    {
        return $this->oauth->beginConnect($workspaceId, $redirectAfter);
    }
}
