<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Contracts\BrandMemoryEnrichmentServiceContract;
use App\Domains\Brand\Contracts\BrandMemoryQueryServiceContract;
use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Data\BrandMemoryContext;
use App\Domains\Brand\Support\HookBrandMemoryPromptComposer;
use Illuminate\Support\Facades\Log;

/**
 * Memory retrieval pipeline → selection → compact prompt composition → fallback.
 */
final class BrandMemoryContextService implements BrandMemoryContextServiceContract
{
    public function __construct(
        private readonly BrandMemoryQueryServiceContract $memoryQuery,
        private readonly BrandMemoryEnrichmentServiceContract $enrichment,
        private readonly WritingSampleRepositoryContract $writingSamples,
        private readonly BrandMemorySelectionService $selection,
        private readonly HookBrandMemoryPromptComposer $promptComposer,
    ) {
    }

    public function forHookAgent(
        string $workspaceId,
        string $hookQueryText,
        ?string $configTargetAudience = null,
        ?string $configContentPillar = null,
        ?int $memoryVersion = null,
    ): BrandMemoryContext {
        $maxSamples = (int) config('ai.hook_agent.max_writing_samples', 2);
        $maxChunks = (int) config('ai.hook_agent.max_memory_chunks', 3);
        $maxSectionChars = (int) config('ai.hook_agent.max_compact_section_chars', 1200);
        $maxChunkChars = (int) config('ai.hook_agent.max_chunk_chars', 400);
        $maxSampleExcerpt = (int) config('ai.hook_agent.max_writing_sample_excerpt_chars', 280);

        $profile = $this->memoryQuery->findPrimaryProfile($workspaceId);

        if ($profile === null) {
            return $this->fallbackContext(
                $workspaceId,
                $configTargetAudience,
                $configContentPillar,
                $memoryVersion,
            );
        }

        $allSamples = $this->writingSamples->listByWorkspace($workspaceId, $maxSamples + 3);
        $samples = $this->selection->selectWritingSamples($allSamples, $maxSamples, $maxSampleExcerpt);

        $enrichment = $this->enrichment->enrich($profile, $samples, $hookQueryText);

        $selectedChunks = $this->selection->selectChunks(
            $enrichment,
            $hookQueryText,
            $maxChunks,
            $maxChunkChars,
        );

        $composed = $this->promptComposer->compose(
            $enrichment,
            $configTargetAudience,
            $configContentPillar,
            $samples,
            $maxSectionChars,
            $maxSampleExcerpt,
        );

        $resolvedVersion = $memoryVersion ?? $profile->memoryVersion;

        return new BrandMemoryContext(
            workspaceId: $workspaceId,
            memoryVersion: $resolvedVersion,
            profileId: $profile->id,
            compactBrandSection: $composed['compact_section'],
            bannedPhrases: $profile->bannedPhrases,
            preferredCtas: $profile->preferredCtas,
            preferredHookPatterns: $profile->preferredHookPatterns,
            selectedChunks: $selectedChunks,
            promptVariables: $composed['variables'],
            personalizationMeta: array_merge($enrichment->analyticsPayload, [
                'hook_agent' => true,
                'selected_chunk_count' => count($selectedChunks),
                'writing_sample_ids' => array_map(static fn ($s) => $s->id, $samples),
                'style_signals' => $composed['style_signals'],
            ]),
            styleSignals: $composed['style_signals'],
            usedFallback: false,
            profile: $profile,
        );
    }

    private function fallbackContext(
        string $workspaceId,
        ?string $configTargetAudience,
        ?string $configContentPillar,
        ?int $memoryVersion,
    ): BrandMemoryContext {
        Log::info('brand.memory.fallback', [
            'workspace_id' => $workspaceId,
            'reason' => 'no_primary_profile',
        ]);

        $emptyEnrichment = new \App\Domains\Brand\Data\BrandMemoryEnrichmentDto(
            workspaceId: $workspaceId,
            memoryVersion: 1,
            profile: null,
            promptVariables: [],
            memoryChunks: [],
            analyticsPayload: ['profile_found' => false],
        );

        $composed = $this->promptComposer->compose(
            $emptyEnrichment,
            $configTargetAudience,
            $configContentPillar,
            [],
            (int) config('ai.hook_agent.max_compact_section_chars', 1200),
            (int) config('ai.hook_agent.max_writing_sample_excerpt_chars', 280),
        );

        return new BrandMemoryContext(
            workspaceId: $workspaceId,
            memoryVersion: $memoryVersion ?? 1,
            profileId: null,
            compactBrandSection: $composed['compact_section'],
            bannedPhrases: [],
            preferredCtas: [],
            preferredHookPatterns: [],
            selectedChunks: [],
            promptVariables: $composed['variables'],
            personalizationMeta: ['profile_found' => false, 'used_fallback' => true],
            styleSignals: [],
            usedFallback: true,
        );
    }
}
