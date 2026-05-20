<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Enums;

enum ScheduledPostStatus: string
{
    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Publishing = 'publishing';
    case Published = 'published';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Scheduled;
    }
}
