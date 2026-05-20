<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationRole: string
{
    case Strategist = 'strategist';
    case Optimization = 'optimization';
    case Analytics = 'analytics';
    case Competitor = 'competitor';
    case Publishing = 'publishing';
    case Recommendation = 'recommendation';
}
