<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class HookVariant extends DataTransferObject
{
    public function __construct(
        public readonly string $text,
        public readonly float $overall,
        public readonly HookScoreDimensions $dimensions,
        public readonly ?string $experimentVariant = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $experimentVariant = null): self
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            overall: (float) ($data['overall'] ?? 0),
            dimensions: HookScoreDimensions::fromArray($data['dimensions'] ?? []),
            experimentVariant: $experimentVariant,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'overall' => $this->overall,
            'dimensions' => $this->dimensions->toArray(),
            'experiment_variant' => $this->experimentVariant,
        ];
    }
}
