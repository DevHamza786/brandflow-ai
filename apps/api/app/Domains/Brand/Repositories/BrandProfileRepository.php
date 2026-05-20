<?php

declare(strict_types=1);

namespace App\Domains\Brand\Repositories;

use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\UpdateBrandProfileDto;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Brand\Support\BrandMemoryNormalizer;
use Illuminate\Database\Eloquent\Builder;

final class BrandProfileRepository implements BrandProfileRepositoryContract
{
    public function __construct(
        private readonly BrandMemoryNormalizer $normalizer,
    ) {
    }

    public function findById(string $workspaceId, string $id): ?BrandProfileDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model ? $this->normalizer->normalizeProfile($model) : null;
    }

    public function findPrimaryByWorkspace(string $workspaceId): ?BrandProfileDto
    {
        $model = $this->scopedQuery($workspaceId)
            ->where('is_primary', true)
            ->orderByDesc('updated_at')
            ->first();

        if ($model === null) {
            $model = $this->scopedQuery($workspaceId)->orderByDesc('updated_at')->first();
        }

        return $model ? $this->normalizer->normalizeProfile($model) : null;
    }

    public function listByWorkspace(string $workspaceId, int $limit = 50): array
    {
        return $this->scopedQuery($workspaceId)
            ->orderByDesc('is_primary')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (BrandProfile $m) => $this->normalizer->normalizeProfile($m))
            ->all();
    }

    public function create(CreateBrandProfileDto $dto): BrandProfileDto
    {
        $tone = $dto->toneProfile ?? new \App\Domains\Brand\Data\ToneProfileDto();
        $audience = $dto->targetAudience ?? new \App\Domains\Brand\Data\AudienceProfileDto();
        $style = $dto->styleGuidelines ?? new \App\Domains\Brand\Data\StyleGuidelinesDto();

        $model = BrandProfile::query()->create([
            'workspace_id' => $dto->workspaceId,
            'name' => $dto->name,
            'brand_voice' => $dto->brandVoice,
            'tone_profile' => $tone->toArray(),
            'target_audience' => $audience->toArray(),
            'banned_phrases' => $dto->bannedPhrases,
            'preferred_ctas' => $dto->preferredCtas,
            'preferred_hook_patterns' => $dto->preferredHookPatterns,
            'style_guidelines' => $style->toArray(),
            'metadata' => $dto->metadata,
            'is_primary' => $dto->isPrimary,
            'memory_version' => 1,
            'voice' => [],
            'pillars' => [],
            'constraints' => [],
        ]);

        return $this->normalizer->normalizeProfile($model);
    }

    public function update(string $workspaceId, string $profileId, UpdateBrandProfileDto $dto): BrandProfileDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $profileId)->firstOrFail();

        if ($dto->name !== null) {
            $model->name = $dto->name;
        }
        if ($dto->brandVoice !== null) {
            $model->brand_voice = $dto->brandVoice;
        }
        if ($dto->toneProfile !== null) {
            $model->tone_profile = $dto->toneProfile->toArray();
        }
        if ($dto->targetAudience !== null) {
            $model->target_audience = $dto->targetAudience->toArray();
        }
        if ($dto->bannedPhrases !== null) {
            $model->banned_phrases = $dto->bannedPhrases;
        }
        if ($dto->preferredCtas !== null) {
            $model->preferred_ctas = $dto->preferredCtas;
        }
        if ($dto->preferredHookPatterns !== null) {
            $model->preferred_hook_patterns = $dto->preferredHookPatterns;
        }
        if ($dto->styleGuidelines !== null) {
            $sg = $dto->styleGuidelines;
            $model->style_guidelines = [
                'summary' => $sg->summary,
                'do' => $sg->doList,
                'dont' => $sg->dontList,
                'max_hook_length' => $sg->maxHookLength,
                'use_emojis' => $sg->useEmojis,
            ];
        }
        if ($dto->metadata !== null) {
            $model->metadata = $dto->metadata;
        }
        if ($dto->isPrimary !== null) {
            if ($dto->isPrimary) {
                $this->scopedQuery($workspaceId)
                    ->where('id', '!=', $profileId)
                    ->update(['is_primary' => false]);
            }
            $model->is_primary = $dto->isPrimary;
        }

        $model->memory_version = (int) $model->memory_version + 1;
        $model->save();

        return $this->normalizer->normalizeProfile($model->fresh());
    }

    public function incrementMemoryVersion(string $workspaceId, string $profileId): BrandProfileDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $profileId)->firstOrFail();
        $model->memory_version = (int) $model->memory_version + 1;
        $model->save();

        return $this->normalizer->normalizeProfile($model->fresh());
    }

    public function setPrimary(string $workspaceId, string $profileId): BrandProfileDto
    {
        $this->scopedQuery($workspaceId)->update(['is_primary' => false]);
        $model = $this->scopedQuery($workspaceId)->where('id', $profileId)->firstOrFail();
        $model->is_primary = true;
        $model->save();

        return $this->normalizer->normalizeProfile($model->fresh());
    }

    /**
     * @return Builder<BrandProfile>
     */
    private function scopedQuery(string $workspaceId): Builder
    {
        return BrandProfile::query()->where('workspace_id', $workspaceId);
    }
}
