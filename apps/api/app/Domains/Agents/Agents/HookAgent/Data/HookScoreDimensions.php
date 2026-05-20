<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class HookScoreDimensions extends DataTransferObject
{
    public function __construct(
        public readonly float $curiosityGap = 0,
        public readonly float $specificity = 0,
        public readonly float $clarity = 0,
        public readonly float $audienceFit = 0,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            curiosityGap: (float) ($data['curiosity_gap'] ?? $data['curiosityGap'] ?? 0),
            specificity: (float) ($data['specificity'] ?? 0),
            clarity: (float) ($data['clarity'] ?? 0),
            audienceFit: (float) ($data['audience_fit'] ?? $data['audienceFit'] ?? 0),
        );
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'curiosity_gap' => $this->curiosityGap,
            'specificity' => $this->specificity,
            'clarity' => $this->clarity,
            'audience_fit' => $this->audienceFit,
        ];
    }
}
