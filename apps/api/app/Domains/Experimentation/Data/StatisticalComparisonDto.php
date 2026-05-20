<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Scientific comparison output for optimization intelligence.
 */
final class StatisticalComparisonDto extends DataTransferObject
{
    public function __construct(
        public readonly string $experimentId,
        public readonly string $winnerVariantKey,
        public readonly string $loserVariantKey,
        public readonly float $liftPercent,
        public readonly float $confidence,
        public readonly bool $isSignificant,
        public readonly string $narrative,
        public readonly int $controlSamples,
        public readonly int $variantSamples,
    ) {
    }
}
