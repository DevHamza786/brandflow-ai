<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Gateway completion request (provider-agnostic).
 */
final class LlmRequest extends DataTransferObject
{
    /**
     * @param  list<AiMessage>  $messages
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $provider,
        public readonly string $model,
        public readonly array $messages,
        public readonly ?StructuredOutputConfig $structuredOutput = null,
        public readonly ?MemoryContext $memoryContext = null,
        public readonly ?int $maxTokens = null,
        public readonly ?float $temperature = null,
        public readonly ?string $traceId = null,
        public readonly ?string $promptSlug = null,
        public readonly ?string $promptVersion = null,
        public readonly array $metadata = [],
    ) {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public static function fromLegacyMessages(
        string $workspaceId,
        string $provider,
        string $model,
        array $messages,
        ?StructuredOutputConfig $structuredOutput = null,
        ?MemoryContext $memoryContext = null,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $traceId = null,
        array $metadata = [],
    ): self {
        return new self(
            workspaceId: $workspaceId,
            provider: $provider,
            model: $model,
            messages: array_map(
                static fn (array $m): AiMessage => AiMessage::fromArray($m),
                $messages
            ),
            structuredOutput: $structuredOutput,
            memoryContext: $memoryContext,
            maxTokens: $maxTokens,
            temperature: $temperature,
            traceId: $traceId,
            metadata: $metadata,
        );
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    public function toProviderMessages(): array
    {
        return array_map(
            static fn (AiMessage $m): array => $m->toProviderArray(),
            $this->messages
        );
    }
}
