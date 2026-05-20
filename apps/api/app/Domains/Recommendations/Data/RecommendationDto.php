<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Data;

use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationStatus;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $evidence
 * @param  array<string, mixed>  $personalizationContext
 * @param  array<string, mixed>  $actionPayload
 * @param  array<string, mixed>  $mlState
 */
final class RecommendationDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly RecommendationType $type,
        public readonly RecommendationStatus $status,
        public readonly RecommendationSource $source,
        public readonly string $correlationKey,
        public readonly string $title,
        public readonly string $summary,
        public readonly ?string $rationale,
        public readonly int $score,
        public readonly ?float $confidence,
        public readonly array $evidence,
        public readonly array $personalizationContext,
        public readonly array $actionPayload,
        public readonly array $mlState,
        public readonly CarbonInterface $generatedAt,
        public readonly ?CarbonInterface $validFrom,
        public readonly ?CarbonInterface $validUntil,
        public readonly ?string $supersededById,
        public readonly ?CarbonInterface $createdAt = null,
    ) {
    }
}
