<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Enums;

enum RecommendationType: string
{
    case HookStyle = 'hook_style';
    case PostingTime = 'posting_time';
    case AudienceFit = 'audience_fit';
    case CtaOptimization = 'cta_optimization';
    case EngagementImprovement = 'engagement_improvement';
    case Personalization = 'personalization';
    case PublishingCadence = 'publishing_cadence';
}
