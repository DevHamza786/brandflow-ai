<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Enums;

enum AutonomousExecutionStatus: string
{
    case Proposed = 'proposed';
    case BlockedLowConfidence = 'blocked_low_confidence';
    case BlockedManualOverride = 'blocked_manual_override';
    case BlockedDuplicate = 'blocked_duplicate';
    case BlockedDisabled = 'blocked_disabled';
    case Approved = 'approved';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
