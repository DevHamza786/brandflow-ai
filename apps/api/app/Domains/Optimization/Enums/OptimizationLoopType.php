<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Enums;

enum OptimizationLoopType: string
{
    case HookStructure = 'hook_structure';
    case PostingTime = 'posting_time';
    case Cta = 'cta';
    case AudienceFit = 'audience_fit';
    case Composite = 'composite';
}
