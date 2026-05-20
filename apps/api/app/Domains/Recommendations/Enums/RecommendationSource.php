<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Enums;

enum RecommendationSource: string
{
    case RecommendationEngine = 'recommendation_engine';
    case HookStyleCorrelation = 'hook_style_correlation';
    case AnalyticsCorrelation = 'analytics_correlation';
    case PostingTimeAnalyzer = 'posting_time_analyzer';
    case AudienceFitEngine = 'audience_fit_engine';
    case CtaOptimizer = 'cta_optimizer';
    case OpportunityDetector = 'opportunity_detector';
    case CompetitorIntelligence = 'competitor_intelligence';
    case OptimizationLoop = 'optimization_loop';
}
