<?php

declare(strict_types=1);

namespace App\Domains\Brand\Repositories;

use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\UpdateWritingSampleDto;
use App\Domains\Brand\Data\WritingSampleDto;
use App\Domains\Brand\Models\WritingSample;
use App\Domains\Brand\Support\BrandMemoryNormalizer;
use Illuminate\Database\Eloquent\Builder;

final class WritingSampleRepository implements WritingSampleRepositoryContract
{
    public function __construct(
        private readonly BrandMemoryNormalizer $normalizer,
    ) {
    }

    public function findById(string $workspaceId, string $id): ?WritingSampleDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model ? $this->normalizer->normalizeSample($model) : null;
    }

    public function listByWorkspace(string $workspaceId, int $limit = 20): array
    {
        return $this->scopedQuery($workspaceId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (WritingSample $m) => $this->normalizer->normalizeSample($m))
            ->all();
    }

    public function listByProfile(string $workspaceId, string $brandProfileId, int $limit = 50): array
    {
        return $this->scopedQuery($workspaceId)
            ->where('brand_profile_id', $brandProfileId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (WritingSample $m) => $this->normalizer->normalizeSample($m))
            ->all();
    }

    public function listEmbeddingReady(string $workspaceId, int $limit = 50): array
    {
        return $this->scopedQuery($workspaceId)
            ->where('embedding_ready', true)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (WritingSample $m) => $this->normalizer->normalizeSample($m))
            ->all();
    }

    public function create(CreateWritingSampleDto $dto, array $normalizedStyleData): WritingSampleDto
    {
        $model = WritingSample::query()->create([
            'workspace_id' => $dto->workspaceId,
            'brand_profile_id' => $dto->brandProfileId,
            'content' => $dto->content,
            'source_type' => $dto->sourceType->value,
            'metadata' => $dto->metadata,
            'normalized_style_data' => $normalizedStyleData,
            'embedding_ready' => $dto->extractStyle && $normalizedStyleData !== [],
        ]);

        return $this->normalizer->normalizeSample($model);
    }

    public function update(string $workspaceId, string $id, UpdateWritingSampleDto $dto, array $normalizedStyleData): WritingSampleDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();

        if ($dto->content !== null) {
            $model->content = $dto->content;
        }
        if ($dto->sourceType !== null) {
            $model->source_type = $dto->sourceType->value;
        }
        if ($dto->metadata !== null) {
            $model->metadata = $dto->metadata;
        }
        if ($normalizedStyleData !== []) {
            $model->normalized_style_data = $normalizedStyleData;
            $model->embedding_ready = true;
        }

        $model->save();

        return $this->normalizer->normalizeSample($model->fresh());
    }

    public function delete(string $workspaceId, string $id): void
    {
        $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail()->delete();
    }

    public function markEmbeddingReady(string $workspaceId, string $id, bool $ready = true): WritingSampleDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->embedding_ready = $ready;
        $model->save();

        return $this->normalizer->normalizeSample($model->fresh());
    }

    /**
     * @return Builder<WritingSample>
     */
    private function scopedQuery(string $workspaceId): Builder
    {
        return WritingSample::query()->where('workspace_id', $workspaceId);
    }
}
