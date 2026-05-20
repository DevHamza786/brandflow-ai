<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Enums;

enum AnalyticsEventType: string
{
    case HookScored = 'hook.scored';

    case PostPerformanceObserved = 'post.performance_observed';

    case EngagementIngested = 'engagement.ingested';

    case WorkflowSignal = 'workflow.signal';
}
