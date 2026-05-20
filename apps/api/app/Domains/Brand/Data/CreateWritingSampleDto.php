<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Shared\Data\DataTransferObject;

final class CreateWritingSampleDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $content,
        public readonly WritingSampleSourceType $sourceType = WritingSampleSourceType::Manual,
        public readonly ?string $brandProfileId = null,
        public readonly array $metadata = [],
        public readonly bool $extractStyle = true,
    ) {
    }
}
