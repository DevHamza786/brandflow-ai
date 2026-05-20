<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Target audience profile — analytics-ready structure.
 */
final class AudienceProfileDto extends DataTransferObject
{
    /**
     * @param  list<string>  $segments
     * @param  list<string>  $painPoints
     * @param  list<string>  $goals
     */
    public function __construct(
        public readonly string $summary = '',
        public readonly array $segments = [],
        public readonly array $painPoints = [],
        public readonly array $goals = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        if (isset($data['summary']) || isset($data['segments'])) {
            return new self(
                summary: (string) ($data['summary'] ?? ''),
                segments: self::stringList($data['segments'] ?? []),
                painPoints: self::stringList($data['pain_points'] ?? $data['painPoints'] ?? []),
                goals: self::stringList($data['goals'] ?? []),
            );
        }

        // Legacy: plain string stored as single key
        if (isset($data['description'])) {
            return new self(summary: (string) $data['description']);
        }

        return new self();
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return is_string($value) && $value !== '' ? [$value] : [];
        }

        return array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $value
        )));
    }
}
