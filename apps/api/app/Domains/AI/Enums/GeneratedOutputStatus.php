<?php

declare(strict_types=1);

namespace App\Domains\AI\Enums;

enum GeneratedOutputStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Superseded = 'superseded';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::Superseded => true,
            default => false,
        };
    }

    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value))
            ?? throw new \InvalidArgumentException("Unknown generated output status [{$value}].");
    }
}
