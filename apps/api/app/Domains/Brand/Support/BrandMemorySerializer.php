<?php

declare(strict_types=1);

namespace App\Domains\Brand\Support;

use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\WritingSampleDto;

/**
 * Stable serialization for analytics pipelines, exports, and vector index metadata.
 */
final class BrandMemorySerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serializeProfile(BrandProfileDto $profile): array
    {
        return [
            'id' => $profile->id,
            'workspace_id' => $profile->workspaceId,
            'name' => $profile->name,
            'brand_voice' => $profile->brandVoice,
            'tone_profile' => $profile->toneProfile->toArray(),
            'target_audience' => $profile->targetAudience->toArray(),
            'banned_phrases' => $profile->bannedPhrases,
            'preferred_ctas' => $profile->preferredCtas,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'style_guidelines' => $profile->styleGuidelines->toArray(),
            'memory_version' => $profile->memoryVersion,
            'pillars' => $profile->pillars,
            'metadata' => $profile->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSample(WritingSampleDto $sample): array
    {
        return [
            'id' => $sample->id,
            'workspace_id' => $sample->workspaceId,
            'brand_profile_id' => $sample->brandProfileId,
            'source_type' => $sample->sourceType->value,
            'content_length' => strlen($sample->content),
            'embedding_ready' => $sample->embeddingReady,
            'normalized_style_data' => $sample->normalizedStyleData->toArray(),
            'metadata' => $sample->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeEnrichment(BrandMemoryEnrichmentDto $enrichment): array
    {
        return [
            'workspace_id' => $enrichment->workspaceId,
            'memory_version' => $enrichment->memoryVersion,
            'profile' => $enrichment->profile !== null
                ? $this->serializeProfile($enrichment->profile)
                : null,
            'chunk_count' => count($enrichment->memoryChunks),
            'prompt_variable_keys' => array_keys($enrichment->promptVariables),
            'analytics' => $enrichment->analyticsPayload,
        ];
    }
}
