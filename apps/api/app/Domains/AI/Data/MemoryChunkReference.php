<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Single memory chunk for RAG injection.
 */
final class MemoryChunkReference extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $content,
        public readonly ?float $score = null,
    ) {
    }

    public function citationTag(): string
    {
        return '[mem:'.$this->id.']';
    }
}
