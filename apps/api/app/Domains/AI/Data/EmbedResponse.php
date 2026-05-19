<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class EmbedResponse extends DataTransferObject
{
    /**
     * @param  array<int, float>  $vector
     */
    public function __construct(
        public readonly array $vector,
        public readonly string $provider,
        public readonly string $model,
        public readonly ?string $traceId = null,
    ) {
    }
}
