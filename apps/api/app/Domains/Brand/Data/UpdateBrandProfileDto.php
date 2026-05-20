<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class UpdateBrandProfileDto extends DataTransferObject
{
    /**
     * @param  list<string>|null  $bannedPhrases
     * @param  list<string>|null  $preferredCtas
     * @param  list<string>|null  $preferredHookPatterns
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $brandVoice = null,
        public readonly ?ToneProfileDto $toneProfile = null,
        public readonly ?AudienceProfileDto $targetAudience = null,
        public readonly ?array $bannedPhrases = null,
        public readonly ?array $preferredCtas = null,
        public readonly ?array $preferredHookPatterns = null,
        public readonly ?StyleGuidelinesDto $styleGuidelines = null,
        public readonly ?array $metadata = null,
        public readonly ?bool $isPrimary = null,
    ) {
    }
}
