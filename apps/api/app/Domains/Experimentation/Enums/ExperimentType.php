<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Enums;

enum ExperimentType: string
{
    case HookAb = 'hook_ab';
    case Cta = 'cta';
    case PostingTime = 'posting_time';
    case Tone = 'tone';
    case AudienceFit = 'audience_fit';
    case AutonomousOptimization = 'autonomous_optimization';
    case RecommendationValidation = 'recommendation_validation';
    case MultiAgent = 'multi_agent';
}
