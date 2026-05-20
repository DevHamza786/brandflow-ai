<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Enums;

/**
 * Publication channel (foundation for LinkedIn-first + future surfaces).
 */
enum SchedulePlatform: string
{
    case LinkedIn = 'linkedin';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::LinkedIn;
    }
}
