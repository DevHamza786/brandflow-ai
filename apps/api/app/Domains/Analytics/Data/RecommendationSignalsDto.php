<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Signals for future recommendation / bandit layers (no model training here).
 *
 * @param  array<string, mixed>  $signals
 */
final class RecommendationSignalsDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly float $normalizedEngagement,
        public readonly ?float $hookPerformanceScore,
        public readonly array $signals,
    ) {
    }
}
