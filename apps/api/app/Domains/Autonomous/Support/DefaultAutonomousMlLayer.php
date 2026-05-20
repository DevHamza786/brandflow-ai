<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Support;

use App\Domains\Autonomous\Contracts\AutonomousMlCompatibilityLayerContract;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;

final class DefaultAutonomousMlLayer implements AutonomousMlCompatibilityLayerContract
{
    public function enrichFeatures(CreateAutonomousExecutionSnapshotDto $draft): array
    {
        return array_merge($draft->mlFeatures, [
            'schema_version' => 1,
            'policy_id' => null,
            'rl_reward' => null,
            'embedding_ref' => null,
            'experiment_arm' => $draft->focus,
            'agent_slug' => null,
            'decision_type' => $draft->decisionType->value,
        ]);
    }
}
