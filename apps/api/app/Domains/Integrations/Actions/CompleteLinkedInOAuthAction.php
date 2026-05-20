<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Services\LinkedInOAuthService;

final class CompleteLinkedInOAuthAction
{
    public function __construct(
        private readonly LinkedInOAuthService $oauth,
    ) {
    }

    /**
     * @return array{integration: LinkedInIntegrationDto, redirect_url: string}
     */
    public function execute(string $state, string $code): array
    {
        return $this->oauth->completeConnect($state, $code);
    }
}
