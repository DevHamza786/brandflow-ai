<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Data;

use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Short-lived OAuth CSRF state stored in cache (not DB).
 */
final class OAuthStateDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $nonce,
        public readonly string $workspaceId,
        public readonly IntegrationProvider $provider,
        public readonly string $redirectAfter,
        public readonly CarbonInterface $expiresAt,
        public readonly array $metadata = [],
    ) {
    }
}
