<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\AI\Data\MemoryChunkReference;
use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\WritingSampleDto;

/**
 * Ranks and truncates memory for hook prompts — semantic retrieval hook point for future vectors.
 */
final class BrandMemorySelectionService
{
    /**
     * @return list<MemoryChunkReference>
     */
    public function selectChunks(
        BrandMemoryEnrichmentDto $enrichment,
        ?string $query,
        int $maxChunks,
        int $maxChunkChars,
    ): array {
        $chunks = $enrichment->memoryChunks;

        usort($chunks, function (MemoryChunkReference $a, MemoryChunkReference $b): int {
            $priority = ['anti_patterns' => 0, 'voice' => 1, 'performance' => 2];
            $pa = $priority[$a->type] ?? 9;
            $pb = $priority[$b->type] ?? 9;

            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            $sa = $a->score ?? 0;
            $sb = $b->score ?? 0;

            return $sb <=> $sa;
        });

        $selected = [];

        foreach (array_slice($chunks, 0, $maxChunks) as $chunk) {
            $content = mb_substr(trim($chunk->content), 0, $maxChunkChars);
            if ($content === '') {
                continue;
            }

            $selected[] = new MemoryChunkReference(
                id: $chunk->id,
                type: $chunk->type,
                content: $content,
                score: $chunk->score,
            );
        }

        return $selected;
    }

    /**
     * @param  list<WritingSampleDto>  $samples
     * @return list<WritingSampleDto>
     */
    public function selectWritingSamples(array $samples, int $maxSamples, int $maxExcerptChars): array
    {
        $ranked = $samples;

        usort($ranked, static function (WritingSampleDto $a, WritingSampleDto $b): int {
            if ($a->embeddingReady !== $b->embeddingReady) {
                return $b->embeddingReady <=> $a->embeddingReady;
            }

            return ($b->updatedAt?->getTimestamp() ?? 0) <=> ($a->updatedAt?->getTimestamp() ?? 0);
        });

        return array_slice($ranked, 0, $maxSamples);
    }
}
