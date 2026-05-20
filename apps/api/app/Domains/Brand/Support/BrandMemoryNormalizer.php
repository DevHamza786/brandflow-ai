<?php

declare(strict_types=1);

namespace App\Domains\Brand\Support;

use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\NormalizedStyleDataDto;
use App\Domains\Brand\Data\StyleGuidelinesDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Data\WritingSampleDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Brand\Models\WritingSample;

/**
 * Maps Eloquent models → typed DTOs; merges legacy voice/pillars into enrichment fields.
 */
final class BrandMemoryNormalizer
{
    public function normalizeProfile(BrandProfile $model): BrandProfileDto
    {
        $legacyVoice = is_array($model->voice) ? $model->voice : [];
        $legacyConstraints = is_array($model->constraints) ? $model->constraints : [];
        $pillars = is_array($model->pillars) ? array_values($model->pillars) : [];

        $brandVoice = trim((string) ($model->brand_voice ?? ''));
        if ($brandVoice === '' && isset($legacyVoice['summary'])) {
            $brandVoice = (string) $legacyVoice['summary'];
        }
        if ($brandVoice === '' && isset($legacyVoice['description'])) {
            $brandVoice = (string) $legacyVoice['description'];
        }

        $toneProfile = ToneProfileDto::fromArray(
            is_array($model->tone_profile) && $model->tone_profile !== []
                ? $model->tone_profile
                : (is_array($legacyVoice['tone'] ?? null) ? $legacyVoice['tone'] : ['primary' => $legacyVoice['tone'] ?? 'professional'])
        );

        $targetAudience = AudienceProfileDto::fromArray(
            is_array($model->target_audience) && $model->target_audience !== []
                ? $model->target_audience
                : (is_array($legacyVoice['audience'] ?? null) ? $legacyVoice['audience'] : [])
        );

        $bannedPhrases = $this->stringList($model->banned_phrases);
        if ($bannedPhrases === [] && isset($legacyConstraints['banned_phrases'])) {
            $bannedPhrases = $this->stringList($legacyConstraints['banned_phrases']);
        }
        if ($bannedPhrases === [] && isset($legacyConstraints['banned_words'])) {
            $bannedPhrases = $this->stringList($legacyConstraints['banned_words']);
        }

        $preferredCtas = $this->stringList($model->preferred_ctas);
        if ($preferredCtas === [] && isset($legacyConstraints['preferred_ctas'])) {
            $preferredCtas = $this->stringList($legacyConstraints['preferred_ctas']);
        }

        $preferredHookPatterns = $this->stringList($model->preferred_hook_patterns);
        if ($preferredHookPatterns === [] && isset($legacyConstraints['hook_patterns'])) {
            $preferredHookPatterns = $this->stringList($legacyConstraints['hook_patterns']);
        }

        $styleGuidelines = StyleGuidelinesDto::fromArray(
            is_array($model->style_guidelines) && $model->style_guidelines !== []
                ? $model->style_guidelines
                : (is_array($legacyVoice['style'] ?? null) ? $legacyVoice['style'] : [])
        );

        $metadata = is_array($model->metadata) ? $model->metadata : [];

        return new BrandProfileDto(
            id: (string) $model->id,
            workspaceId: (string) $model->workspace_id,
            name: (string) $model->name,
            brandVoice: $brandVoice,
            toneProfile: $toneProfile,
            targetAudience: $targetAudience,
            bannedPhrases: $bannedPhrases,
            preferredCtas: $preferredCtas,
            preferredHookPatterns: $preferredHookPatterns,
            styleGuidelines: $styleGuidelines,
            memoryVersion: (int) $model->memory_version,
            isPrimary: (bool) $model->is_primary,
            metadata: $metadata,
            pillars: $pillars,
            legacyVoice: $legacyVoice,
            legacyConstraints: $legacyConstraints,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function normalizeSample(WritingSample $model): WritingSampleDto
    {
        $normalized = is_array($model->normalized_style_data) ? $model->normalized_style_data : [];

        return new WritingSampleDto(
            id: (string) $model->id,
            workspaceId: (string) $model->workspace_id,
            brandProfileId: $model->brand_profile_id,
            content: (string) $model->content,
            sourceType: WritingSampleSourceType::fromString((string) $model->source_type),
            metadata: is_array($model->metadata) ? $model->metadata : [],
            embeddingReady: (bool) $model->embedding_ready,
            normalizedStyleData: NormalizedStyleDataDto::fromArray($normalized),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return is_string($value) && trim($value) !== '' ? [trim($value)] : [];
        }

        return array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $value
        )));
    }
}
