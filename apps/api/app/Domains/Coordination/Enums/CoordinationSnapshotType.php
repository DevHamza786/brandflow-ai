<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationSnapshotType: string
{
    case Routing = 'routing';
    case ContextShare = 'context_share';
    case AgentDispatch = 'agent_dispatch';
    case AgentComplete = 'agent_complete';
    case AgentFailed = 'agent_failed';
    case Recovery = 'recovery';
    case IntegrationComplete = 'integration_complete';
}
