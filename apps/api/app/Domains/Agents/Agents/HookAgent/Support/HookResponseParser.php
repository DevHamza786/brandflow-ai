<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Support;

use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookScoringFailedException;
use App\Domains\AI\Data\AiResponse;

/**
 * Parses structured LLM responses into Hook Lab DTOs.
 */
final class HookResponseParser
{
    public function parseScorer(AiResponse $response, string $hookText, string $promptVersion): HookResult
    {
        if ($response->structured === null) {
            throw new HookScoringFailedException(
                'Hook scorer returned no structured payload.',
                ['trace_id' => $response->traceId]
            );
        }

        return HookResult::fromStructured($response->structured, $hookText, $promptVersion);
    }

    /**
     * @return list<HookVariant>
     */
    public function parseVariants(AiResponse $response, ?string $experimentId = null): array
    {
        if ($response->structured === null) {
            throw new HookScoringFailedException(
                'Hook variant generator returned no structured payload.',
                ['trace_id' => $response->traceId]
            );
        }

        $rawVariants = $response->structured['variants'] ?? [];

        if (! is_array($rawVariants)) {
            return [];
        }

        $variants = [];

        foreach ($rawVariants as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = $experimentId !== null ? "{$experimentId}_variant_{$index}" : null;
            $variants[] = HookVariant::fromArray($item, $label);
        }

        return $variants;
    }
}
