<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class CreateBrandProfileDto extends DataTransferObject
{
    /**
     * @param  list<string>  $bannedPhrases
     * @param  list<string>  $preferredCtas
     * @param  list<string>  $preferredHookPatterns
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $name = 'Default',
        public readonly ?string $brandVoice = null,
        public readonly ?ToneProfileDto $toneProfile = null,
        public readonly ?AudienceProfileDto $targetAudience = null,
        public readonly array $bannedPhrases = [],
        public readonly array $preferredCtas = [],
        public readonly array $preferredHookPatterns = [],
        public readonly ?StyleGuidelinesDto $styleGuidelines = null,
        public readonly array $metadata = [],
        public readonly bool $isPrimary = true,
    ) {
    }
}
