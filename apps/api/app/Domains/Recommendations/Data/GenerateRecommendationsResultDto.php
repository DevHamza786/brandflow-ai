<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<RecommendationDto>  $recommendations
 * @param  array<string, int>  $countsByType
 */
final class GenerateRecommendationsResultDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $generatedCount,
        public readonly int $supersededCount,
        public readonly array $recommendations,
        public readonly array $countsByType,
    ) {
    }
}
