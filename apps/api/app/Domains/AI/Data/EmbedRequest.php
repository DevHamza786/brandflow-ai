<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class EmbedRequest extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $provider,
        public readonly string $model,
        public readonly string $input,
    ) {
    }
}
