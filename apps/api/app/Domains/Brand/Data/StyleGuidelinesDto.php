<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class StyleGuidelinesDto extends DataTransferObject
{
    /**
     * @param  list<string>  $doList
     * @param  list<string>  $dontList
     */
    public function __construct(
        public readonly string $summary = '',
        public readonly array $doList = [],
        public readonly array $dontList = [],
        public readonly ?int $maxHookLength = null,
        public readonly ?bool $useEmojis = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            summary: (string) ($data['summary'] ?? ''),
            doList: self::stringList($data['do'] ?? $data['do_list'] ?? []),
            dontList: self::stringList($data['dont'] ?? $data['dont_list'] ?? []),
            maxHookLength: isset($data['max_hook_length']) ? (int) $data['max_hook_length'] : null,
            useEmojis: isset($data['use_emojis']) ? (bool) $data['use_emojis'] : null,
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
