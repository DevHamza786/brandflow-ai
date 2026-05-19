<?php

declare(strict_types=1);

namespace App\Queue\Jobs\Concerns;

use App\Queue\Support\JobTagger;

/**
 * Standard Horizon tags for workspace-scoped jobs.
 */
trait HasQueueTags
{
    /**
     * @return array<int, string>
     */
    public function baseTags(): array
    {
        return [
            JobTagger::workspace($this->workspaceId),
            JobTagger::queue($this->queueName()),
        ];
    }
}
