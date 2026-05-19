<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Primary hook scoring result for the current opening lines.
 */
final class HookResult extends DataTransferObject
{
    /**
     * @param  list<string>  $suggestions
     */
    public function __construct(
        public readonly string $hookText,
        public readonly float $overall,
        public readonly HookScoreDimensions $dimensions,
        public readonly array $suggestions = [],
        public readonly ?string $promptVersion = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromStructured(array $data, string $hookText, ?string $promptVersion = null): self
    {
        return new self(
            hookText: $hookText,
            overall: (float) ($data['overall'] ?? 0),
            dimensions: HookScoreDimensions::fromArray($data['dimensions'] ?? []),
            suggestions: array_values($data['suggestions'] ?? []),
            promptVersion: $promptVersion,
        );
    }
}
