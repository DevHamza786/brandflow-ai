<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Enums;

/**
 * Scheduling cadence. Recurring engine stores payloads in recurrence_rule JSON (RFC 5545 subset later).
 */
enum SchedulePattern: string
{
    case Once = 'once';
    case Recurring = 'recurring';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Once;
    }
}
