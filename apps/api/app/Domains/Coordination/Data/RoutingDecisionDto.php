<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Coordination\Enums\CoordinationRole;
use App\Domains\Coordination\Enums\CoordinationTaskType;
use App\Domains\Shared\Data\DataTransferObject;

final class RoutingDecisionDto extends DataTransferObject
{
    public function __construct(
        public readonly CoordinationTaskType $taskType,
        public readonly CoordinationRole $role,
        public readonly CoordinationHandlerType $handlerType,
        public readonly ?string $agentSlug,
        public readonly ?string $fallbackAgentSlug,
        public readonly int $priority,
    ) {
    }
}
