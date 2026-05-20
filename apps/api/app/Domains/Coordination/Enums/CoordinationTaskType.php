<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationTaskType: string
{
    case StrategistPlan = 'strategist_plan';
    case AnalyticsInsights = 'analytics_insights';
    case OptimizationCycle = 'optimization_cycle';
    case CompetitorAnalysis = 'competitor_analysis';
    case PublishingSchedule = 'publishing_schedule';
    case RecommendationGenerate = 'recommendation_generate';
    case HookGeneration = 'hook_generation';
}
