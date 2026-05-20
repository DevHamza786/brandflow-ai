<?php

declare(strict_types=1);

namespace App\Domains\Agents\Support;

use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Content\Models\HookScore;

/**
 * Normalizes domain persistence into frontend-safe result fragments.
 */
final class AgentRunOutputNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function normalizeGeneratedOutput(GeneratedOutputDto $dto): array
    {
        $payload = $dto->output?->payload ?? [];
        $hook = $this->normalizeHookPayload($payload, $dto);

        return [
            'id' => $dto->id,
            'type' => $dto->type->value,
            'status' => $dto->status->value,
            'provider' => $dto->provider,
            'model' => $dto->model,
            'prompt_version' => $dto->promptVersion,
            'primary' => $hook['primary'] ?? null,
            'variants' => $hook['variants'],
            'dimensions' => $hook['dimensions'],
            'suggestions' => $hook['suggestions'],
            'scores' => $this->normalizeScoresFromDto($dto),
            'metadata' => $this->publicMetadata($dto),
            'created_at' => $dto->createdAt?->toIso8601String(),
            'updated_at' => $dto->updatedAt?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeHookScore(HookScore $hookScore): array
    {
        return [
            'id' => $hookScore->id,
            'type' => GeneratedOutputType::Hook->value,
            'status' => 'completed',
            'provider' => null,
            'model' => $hookScore->model,
            'prompt_version' => $hookScore->prompt_version,
            'primary' => [
                'overall' => (float) $hookScore->score,
                'dimensions' => $hookScore->dimensions ?? [],
                'suggestions' => $hookScore->suggestions ?? [],
            ],
            'variants' => $hookScore->variants ?? [],
            'dimensions' => $hookScore->dimensions ?? [],
            'suggestions' => $hookScore->suggestions ?? [],
            'scores' => [
                'overall' => (float) $hookScore->score,
                'dimensions' => $hookScore->dimensions ?? [],
            ],
            'metadata' => [
                'trace_id' => $hookScore->trace_id,
                'legacy_hook_score' => true,
            ],
            'created_at' => $hookScore->created_at?->toIso8601String(),
            'updated_at' => $hookScore->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $outputs
     * @return array{
     *     scores: array<string, mixed>,
     *     variants: list<array<string, mixed>>,
     *     dimensions: array<string, mixed>,
     *     suggestions: list<string>
     * }
     */
    public function aggregatePrimaryFields(array $outputs): array
    {
        $primary = $outputs[0] ?? null;

        if ($primary === null) {
            return [
                'scores' => [],
                'variants' => [],
                'dimensions' => [],
                'suggestions' => [],
            ];
        }

        return [
            'scores' => is_array($primary['scores'] ?? null) ? $primary['scores'] : [],
            'variants' => is_array($primary['variants'] ?? null) ? $primary['variants'] : [],
            'dimensions' => is_array($primary['dimensions'] ?? null) ? $primary['dimensions'] : [],
            'suggestions' => is_array($primary['suggestions'] ?? null) ? $primary['suggestions'] : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     primary: array<string, mixed>|null,
     *     variants: list<array<string, mixed>>,
     *     dimensions: array<string, mixed>,
     *     suggestions: list<string>
     * }
     */
    private function normalizeHookPayload(array $payload, GeneratedOutputDto $dto): array
    {
        $primary = is_array($payload['primary'] ?? null) ? $payload['primary'] : null;
        $variants = is_array($payload['variants'] ?? null) ? array_values($payload['variants']) : [];
        $dimensions = is_array($primary['dimensions'] ?? null)
            ? $primary['dimensions']
            : (is_array($dto->scores->dimensions) ? $dto->scores->dimensions : []);
        $suggestions = is_array($primary['suggestions'] ?? null)
            ? array_values($primary['suggestions'])
            : [];

        return [
            'primary' => $primary,
            'variants' => $variants,
            'dimensions' => $dimensions,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeScoresFromDto(GeneratedOutputDto $dto): array
    {
        $scores = $dto->scores->toArray();
        $variantScores = [];

        if (isset($scores['extras']['variant_scores']) && is_array($scores['extras']['variant_scores'])) {
            $variantScores = $scores['extras']['variant_scores'];
        }

        return array_filter([
            'overall' => $scores['overall'] ?? null,
            'dimensions' => $scores['dimensions'] ?? [],
            'variants' => $variantScores !== [] ? $variantScores : null,
        ], static fn ($v) => $v !== null && $v !== []);
    }

    /**
     * @return array<string, mixed>
     */
    private function publicMetadata(GeneratedOutputDto $dto): array
    {
        $stored = $dto->metadata->toStorageArray();

        foreach (['raw_prompt', 'raw_response', 'api_key', 'session_ref'] as $key) {
            unset($stored[$key]);
        }

        return $stored;
    }
}
