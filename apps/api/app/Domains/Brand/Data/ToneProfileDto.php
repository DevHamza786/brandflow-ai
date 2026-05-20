<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Structured tone profile for prompt enrichment and analytics.
 */
final class ToneProfileDto extends DataTransferObject
{
    /**
     * @param  list<string>  $traits
     * @param  list<string>  $avoid
     */
    public function __construct(
        public readonly string $primary = 'professional',
        public readonly array $traits = [],
        public readonly array $avoid = [],
        public readonly ?float $formality = null,
        public readonly ?float $energy = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            primary: (string) ($data['primary'] ?? $data['tone'] ?? 'professional'),
            traits: self::stringList($data['traits'] ?? []),
            avoid: self::stringList($data['avoid'] ?? $data['avoid_tones'] ?? []),
            formality: isset($data['formality']) ? (float) $data['formality'] : null,
            energy: isset($data['energy']) ? (float) $data['energy'] : null,
        );
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $value
        )));
    }
}
