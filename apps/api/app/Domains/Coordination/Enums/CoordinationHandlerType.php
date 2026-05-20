<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationHandlerType: string
{
    case Agent = 'agent';
    case Optimization = 'optimization';
    case Recommendation = 'recommendation';
    case Publishing = 'publishing';
    case Deferred = 'deferred';
}
