<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Data;

use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class UpdateLinkedInIntegrationTokensDto extends DataTransferObject
{
    /**
     * @param  list<string>|null  $scopes
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonInterface $tokenExpiresAt = null,
        public readonly ?array $scopes = null,
        public readonly ?array $metadata = null,
        public readonly ?IntegrationStatus $status = null,
        public readonly ?string $linkedinMemberId = null,
        public readonly bool $resetRefreshAttempts = true,
    ) {
    }
}
