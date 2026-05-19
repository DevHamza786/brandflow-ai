<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class EmbedResponse extends DataTransferObject
{
    /**
     * @param  list<float>  $vector
     */
    public function __construct(
        public readonly array $vector,
        public readonly string $provider,
        public readonly string $model,
        public readonly TokenUsage $tokenUsage,
        public readonly ?string $traceId = null,
    ) {
    }
}
