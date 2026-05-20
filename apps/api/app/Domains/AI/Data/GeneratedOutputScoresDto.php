<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Structured scoring payload (dimensions, overall, rubric version).
 */
final class GeneratedOutputScoresDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $dimensions
     * @param  array<string, mixed>  $extras
     */
    public function __construct(
        public readonly ?float $overall = null,
        public readonly array $dimensions = [],
        public readonly array $extras = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): static
    {
        return new self(
            overall: isset($payload['overall']) ? (float) $payload['overall'] : null,
            dimensions: is_array($payload['dimensions'] ?? null) ? $payload['dimensions'] : [],
            extras: array_diff_key($payload, array_flip(['overall', 'dimensions'])),
        );
    }
}
