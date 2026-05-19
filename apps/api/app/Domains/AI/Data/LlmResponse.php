<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class LlmResponse extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $usage
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly array $usage = [],
        public readonly ?string $traceId = null,
        public readonly array $metadata = [],
    ) {
    }
}
