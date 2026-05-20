<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Models\WritingSample;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class WritingSampleDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly ?string $brandProfileId,
        public readonly string $content,
        public readonly WritingSampleSourceType $sourceType,
        public readonly array $metadata,
        public readonly bool $embeddingReady,
        public readonly NormalizedStyleDataDto $normalizedStyleData,
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $updatedAt = null,
    ) {
    }

    public static function fromModel(WritingSample $model): self
    {
        return app(\App\Domains\Brand\Support\BrandMemoryNormalizer::class)->normalizeSample($model);
    }
}
