<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Shared\Data\DataTransferObject;

final class UpdateWritingSampleDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly ?string $content = null,
        public readonly ?WritingSampleSourceType $sourceType = null,
        public readonly ?array $metadata = null,
        public readonly ?bool $reextractStyle = null,
    ) {
    }
}
