<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Enums;

enum AutonomousDecisionType: string
{
    case PostingTime = 'posting_time';
    case ContentSelection = 'content_selection';
    case PostingDecision = 'posting_decision';
    case Composite = 'composite';
}
