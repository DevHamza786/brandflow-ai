<?php

declare(strict_types=1);

namespace App\Domains\Shared\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Base queued job with PBOS retry, backoff, and Horizon tagging conventions.
 *
 * @see docs/AGENTS.md §7 Queue Rules
 */
abstract class BaseQueueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public readonly string $workspaceId,
    ) {
        $this->onQueue($this->queueName());
    }

    abstract public function queueName(): string;

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'workspace:'.$this->workspaceId,
        ];
    }
}
