<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Brand\Data\WritingSampleDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WritingSampleDto
 */
final class WritingSampleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WritingSampleDto $sample */
        $sample = $this->resource;

        return [
            'id' => $sample->id,
            'workspace_id' => $sample->workspaceId,
            'brand_profile_id' => $sample->brandProfileId,
            'content' => $sample->content,
            'source_type' => $sample->sourceType->value,
            'metadata' => $sample->metadata,
            'embedding_ready' => $sample->embeddingReady,
            'normalized_style_data' => $sample->normalizedStyleData->toArray(),
            'created_at' => $sample->createdAt?->toIso8601String(),
            'updated_at' => $sample->updatedAt?->toIso8601String(),
        ];
    }
}
