<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Coordination\Enums\CoordinationRole;
use App\Domains\Coordination\Enums\CoordinationTaskType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $input
 */
final class CoordinationTaskDto extends DataTransferObject
{
    public function __construct(
        public readonly CoordinationTaskType $taskType,
        public readonly CoordinationRole $role,
        public readonly int $priority = 100,
        public readonly array $input = [],
        public readonly bool $isolated = true,
        public readonly ?string $fallbackRole = null,
    ) {
    }
}
