<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Jobs;

use App\Domains\Integrations\Exceptions\UnretryablePublishingException;
use App\Domains\Schedule\Services\LinkedInPublishingService;
use App\Queue\Enums\QueueName;
use App\Queue\Jobs\AbstractQueueJob;

/**
 * Executes LinkedIn publishing for one scheduled_posts row (`scheduling` queue).
 *
 * Idempotency: atomic orchestration (`scheduled→queued`), publish-side cache lock,
 * LinkedInPublishingService skips already-published rows.
 */
final class PublishLinkedInPostJob extends AbstractQueueJob
{
    public function __construct(
        string $workspaceId,
        public readonly string $scheduledPostId,
    ) {
        parent::__construct($workspaceId);
    }

    public function queueName(): string
    {
        return QueueName::Scheduling->value;
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return array_merge(parent::tags(), [
            'scheduled_post:'.$this->scheduledPostId,
        ]);
    }

    public function handle(LinkedInPublishingService $publishing): void
    {
        try {
            $publishing->publishScheduledPost($this->workspaceId, $this->scheduledPostId);
        } catch (UnretryablePublishingException) {
            // Persisted as failed in LinkedInPublishingService — avoid pointless retries.
        }
    }
}
