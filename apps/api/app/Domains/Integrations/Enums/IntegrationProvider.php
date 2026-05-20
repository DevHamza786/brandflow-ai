<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Enums;

enum IntegrationProvider: string
{
    case LinkedIn = 'linkedin';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::LinkedIn;
    }
}
