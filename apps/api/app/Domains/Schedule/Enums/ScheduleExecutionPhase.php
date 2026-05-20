<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Enums;

/**
 * Persisted orchestration milestones (schedule_execution_events.phase).
 */
enum ScheduleExecutionPhase: string
{
    case BatchOpened = 'batch_opened';

    case RowClaimed = 'row_claimed';

    case DispatchQueuedJob = 'dispatch_queued_publish_job';

    case StaleRecoverRedispatch = 'stale_recover_redispatch';

    case BatchClosed = 'batch_closed';
}
