<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Data;

use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class CreateLinkedInIntegrationDto extends DataTransferObject
{
    /**
     * @param  list<string>  $scopes
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonInterface $tokenExpiresAt = null,
        public readonly ?string $linkedinMemberId = null,
        public readonly array $scopes = [],
        public readonly array $metadata = [],
        public readonly IntegrationProvider $provider = IntegrationProvider::LinkedIn,
        public readonly IntegrationStatus $status = IntegrationStatus::Connected,
    ) {
    }
}
