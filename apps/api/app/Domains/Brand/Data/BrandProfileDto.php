<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class BrandProfileDto extends DataTransferObject
{
    /**
     * @param  list<string>  $bannedPhrases
     * @param  list<string>  $preferredCtas
     * @param  list<string>  $preferredHookPatterns
     * @param  list<string>  $pillars
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $legacyVoice
     * @param  array<string, mixed>  $legacyConstraints
     */
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $name,
        public readonly string $brandVoice,
        public readonly ToneProfileDto $toneProfile,
        public readonly AudienceProfileDto $targetAudience,
        public readonly array $bannedPhrases,
        public readonly array $preferredCtas,
        public readonly array $preferredHookPatterns,
        public readonly StyleGuidelinesDto $styleGuidelines,
        public readonly int $memoryVersion,
        public readonly bool $isPrimary,
        public readonly array $metadata,
        public readonly array $pillars,
        public readonly array $legacyVoice,
        public readonly array $legacyConstraints,
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $updatedAt = null,
    ) {
    }

    public static function fromModel(BrandProfile $model): self
    {
        return app(\App\Domains\Brand\Support\BrandMemoryNormalizer::class)->normalizeProfile($model);
    }
}
