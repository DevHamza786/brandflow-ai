<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\AI\Data\MemoryChunkReference;
use App\Domains\Brand\Contracts\BrandMemoryEnrichmentServiceContract;
use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\WritingSampleDto;
use App\Domains\Brand\Support\BrandMemoryPromptInjector;
/**
 * Assembles prompt variables + memory chunk references from brand profile and samples.
 */
final class BrandMemoryEnrichmentService implements BrandMemoryEnrichmentServiceContract
{
    public function __construct(
        private readonly BrandMemoryPromptInjector $promptInjector,
    ) {
    }

    public function enrich(
        BrandProfileDto $profile,
        array $samples = [],
        ?string $query = null,
    ): BrandMemoryEnrichmentDto {
        $promptVariables = $this->promptInjector->buildPromptVariables($profile, $samples);
        $promptVariables['brand_memory_section'] = $this->promptInjector->toSystemPromptSection($profile, $samples);

        $chunks = $this->buildMemoryChunks($profile, $samples, $query);

        $analyticsPayload = [
            'workspace_id' => $profile->workspaceId,
            'profile_id' => $profile->id,
            'memory_version' => $profile->memoryVersion,
            'banned_phrase_count' => count($profile->bannedPhrases),
            'preferred_cta_count' => count($profile->preferredCtas),
            'hook_pattern_count' => count($profile->preferredHookPatterns),
            'writing_sample_count' => count($samples),
            'embedding_ready_count' => count(array_filter($samples, static fn (WritingSampleDto $s) => $s->embeddingReady)),
            'tone_primary' => $profile->toneProfile->primary,
            'vector_search_ready' => false,
            'query' => $query,
        ];

        return new BrandMemoryEnrichmentDto(
            workspaceId: $profile->workspaceId,
            memoryVersion: $profile->memoryVersion,
            profile: $profile,
            promptVariables: $promptVariables,
            memoryChunks: $chunks,
            analyticsPayload: $analyticsPayload,
        );
    }

    /**
     * @param  list<WritingSampleDto>  $samples
     * @return list<MemoryChunkReference>
     */
    private function buildMemoryChunks(
        BrandProfileDto $profile,
        array $samples,
        ?string $query,
    ): array {
        $chunks = [];

        $voiceSection = $this->promptInjector->toSystemPromptSection($profile, []);
        if ($voiceSection !== '') {
            $chunks[] = new MemoryChunkReference(
                id: 'brand_profile:'.$profile->id,
                type: 'voice',
                content: $voiceSection,
            );
        }

        foreach (array_slice($samples, 0, 5) as $sample) {
            $excerpt = mb_substr($sample->content, 0, 600);
            $chunks[] = new MemoryChunkReference(
                id: 'writing_sample:'.$sample->id,
                type: 'voice',
                content: $excerpt,
                score: $this->relevanceScore($excerpt, $query),
            );
        }

        if ($profile->bannedPhrases !== []) {
            $chunks[] = new MemoryChunkReference(
                id: 'brand_constraints:'.$profile->id,
                type: 'anti_patterns',
                content: 'Banned phrases: '.implode('; ', $profile->bannedPhrases),
            );
        }

        return $chunks;
    }

    private function relevanceScore(string $excerpt, ?string $query): ?float
    {
        if ($query === null || trim($query) === '') {
            return null;
        }

        $percent = 0.0;
        similar_text(mb_strtolower($excerpt), mb_strtolower($query), $percent);

        return $percent > 0 ? round($percent / 100, 4) : null;
    }
}
