<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  list<string>  $labels
 * @param  array<string, mixed>  $metadata
 */
final class CompetitorDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $linkedinUrl,
        public readonly ?string $name,
        public readonly ?string $linkedinUrn,
        public readonly array $labels,
        public readonly array $metadata,
        public readonly int $scrapeCadenceHours,
        public readonly ?CarbonInterface $lastScrapedAt,
        public readonly ?CarbonInterface $lastAnalyzedAt,
        public readonly ?float $intelligenceScore,
        public readonly bool $isActive,
        public readonly ?CarbonInterface $createdAt = null,
    ) {
    }
}
