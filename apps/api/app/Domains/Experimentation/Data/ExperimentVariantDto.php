<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $payload
 * @param  array<string, mixed>  $metadata
 */
final class ExperimentVariantDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $experimentId,
        public readonly string $variantKey,
        public readonly ?string $label,
        public readonly bool $isControl,
        public readonly float $trafficWeight,
        public readonly array $payload,
        public readonly array $metadata,
        public readonly int $assignmentCount,
    ) {
    }
}
