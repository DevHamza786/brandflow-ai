<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Events;

use App\Domains\Schedule\Data\ScheduledPostDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ScheduledPostPublishingStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ScheduledPostDto $scheduledPost,
        public readonly string $traceId,
    ) {
    }
}
