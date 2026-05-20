<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Events;

use App\Domains\Schedule\Data\ScheduledPostDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @param  array<string, mixed>  $errorDetails
 */
final class ScheduledPostPublishFailed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $errorDetails
     */
    public function __construct(
        public readonly ScheduledPostDto $scheduledPost,
        public readonly string $traceId,
        public readonly array $errorDetails = [],
    ) {
    }
}
