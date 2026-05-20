<?php

declare(strict_types=1);

namespace App\Domains\Agents\Events;

use App\Domains\Agents\Models\AgentRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AgentRunCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly AgentRun $agentRun,
    ) {
    }
}
