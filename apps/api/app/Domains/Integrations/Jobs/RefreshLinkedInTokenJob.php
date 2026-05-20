<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Jobs;

use App\Domains\Integrations\Services\LinkedInTokenRefreshService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Async token refresh — scheduler dispatches before expiry (future scheduling engine).
 */
final class RefreshLinkedInTokenJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $integrationId,
        public readonly ?string $traceId = null,
    ) {
        $this->onQueue('default');
    }

    public function handle(LinkedInTokenRefreshService $refresh): void
    {
        $refresh->refresh($this->workspaceId, $this->integrationId, $this->traceId);
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return [
            'workspace:'.$this->workspaceId,
            'integration:linkedin',
            'integration_id:'.$this->integrationId,
        ];
    }
}
