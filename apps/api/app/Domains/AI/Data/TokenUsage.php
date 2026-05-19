<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Normalized token usage across providers.
 */
final class TokenUsage extends DataTransferObject
{
    public function __construct(
        public readonly int $promptTokens = 0,
        public readonly int $completionTokens = 0,
        public readonly int $totalTokens = 0,
        public readonly ?float $estimatedCostUsd = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function fromProviderUsage(array $raw): self
    {
        $prompt = (int) ($raw['prompt_tokens'] ?? $raw['input_tokens'] ?? 0);
        $completion = (int) ($raw['completion_tokens'] ?? $raw['output_tokens'] ?? 0);
        $total = (int) ($raw['total_tokens'] ?? ($prompt + $completion));

        return new self(
            promptTokens: $prompt,
            completionTokens: $completion,
            totalTokens: $total,
            estimatedCostUsd: isset($raw['estimated_cost_usd']) ? (float) $raw['estimated_cost_usd'] : null,
        );
    }

    /**
     * @return array<string, int|float|null>
     */
    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
            'estimated_cost_usd' => $this->estimatedCostUsd,
        ];
    }
}
