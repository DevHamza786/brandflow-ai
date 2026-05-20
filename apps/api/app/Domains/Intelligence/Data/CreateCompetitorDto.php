<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<string>  $labels
 * @param  array<string, mixed>  $metadata
 */
final class CreateCompetitorDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $linkedinUrl,
        public readonly ?string $name = null,
        public readonly ?string $linkedinUrn = null,
        public readonly array $labels = [],
        public readonly array $metadata = [],
        public readonly int $scrapeCadenceHours = 24,
    ) {
    }
}
