<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Enums;

enum IntegrationStatus: string
{
    case Pending = 'pending';
    case Connected = 'connected';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Error = 'error';
    case Disconnected = 'disconnected';

    public function isActive(): bool
    {
        return $this === self::Connected;
    }

    public function allowsRefresh(): bool
    {
        return in_array($this, [self::Connected, self::Expired, self::Error], true);
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Pending;
    }
}
