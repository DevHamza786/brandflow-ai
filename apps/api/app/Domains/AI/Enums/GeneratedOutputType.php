<?php

declare(strict_types=1);

namespace App\Domains\AI\Enums;

/**
 * Canonical output kinds aligned with agent slugs and workflow steps.
 */
enum GeneratedOutputType: string
{
    case Hook = 'hook';
    case Profile = 'profile';
    case Analytics = 'analytics';
    case Competitor = 'competitor';
    case Reply = 'reply';
    case Carousel = 'carousel';
    case Custom = 'custom';

    public static function fromAgentSlug(string $slug): self
    {
        return self::tryFrom($slug) ?? self::Custom;
    }

    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value))
            ?? throw new \InvalidArgumentException("Unknown generated output type [{$value}].");
    }
}
