<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Brand\Data\BrandProfileDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BrandProfileDto
 */
final class BrandProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var BrandProfileDto $profile */
        $profile = $this->resource;

        return [
            'id' => $profile->id,
            'workspace_id' => $profile->workspaceId,
            'name' => $profile->name,
            'brand_voice' => $profile->brandVoice,
            'tone_profile' => [
                'primary' => $profile->toneProfile->primary,
                'traits' => $profile->toneProfile->traits,
                'avoid' => $profile->toneProfile->avoid,
                'formality' => $profile->toneProfile->formality,
                'energy' => $profile->toneProfile->energy,
            ],
            'target_audience' => [
                'summary' => $profile->targetAudience->summary,
                'segments' => $profile->targetAudience->segments,
                'pain_points' => $profile->targetAudience->painPoints,
                'goals' => $profile->targetAudience->goals,
            ],
            'banned_phrases' => $profile->bannedPhrases,
            'preferred_ctas' => $profile->preferredCtas,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'style_guidelines' => [
                'summary' => $profile->styleGuidelines->summary,
                'do' => $profile->styleGuidelines->doList,
                'dont' => $profile->styleGuidelines->dontList,
                'max_hook_length' => $profile->styleGuidelines->maxHookLength,
                'use_emojis' => $profile->styleGuidelines->useEmojis,
            ],
            'memory_version' => $profile->memoryVersion,
            'is_primary' => $profile->isPrimary,
            'metadata' => $profile->metadata,
            'pillars' => $profile->pillars,
            'created_at' => $profile->createdAt?->toIso8601String(),
            'updated_at' => $profile->updatedAt?->toIso8601String(),
        ];
    }
}
