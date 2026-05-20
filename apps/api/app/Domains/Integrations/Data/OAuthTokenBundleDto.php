<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Normalized token payload from any OAuth provider.
 */
final class OAuthTokenBundleDto extends DataTransferObject
{
    /**
     * @param  list<string>  $scopes
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonInterface $expiresAt = null,
        public readonly array $scopes = [],
        public readonly array $raw = [],
    ) {
    }
}
