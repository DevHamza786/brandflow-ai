<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Brand\Data\BrandMemoryContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BrandMemoryContext
 */
final class BrandMemoryPreviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var BrandMemoryContext $ctx */
        $ctx = $this->resource;

        return [
            'compact_brand_section' => $ctx->compactBrandSection,
            'compact_section_chars' => strlen($ctx->compactBrandSection),
            'banned_phrases' => $ctx->bannedPhrases,
            'preferred_ctas' => $ctx->preferredCtas,
            'preferred_hook_patterns' => $ctx->preferredHookPatterns,
            'memory_version' => $ctx->memoryVersion,
            'profile_id' => $ctx->profileId,
            'used_fallback' => $ctx->usedFallback,
            'style_signals' => $ctx->styleSignals,
            'personalization_meta' => $ctx->personalizationMeta,
            'chunk_ids' => array_map(
                static fn ($c) => $c->id,
                $ctx->selectedChunks,
            ),
            'analytics' => $ctx->toAnalyticsPayload(),
        ];
    }
}
