<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class LlmRequest extends DataTransferObject
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $provider,
        public readonly string $model,
        public readonly array $messages,
        public readonly ?string $responseFormat = null,
        public readonly ?int $maxTokens = null,
        public readonly ?float $temperature = null,
        public readonly array $metadata = [],
    ) {
    }
}
