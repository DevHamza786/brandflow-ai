<?php

declare(strict_types=1);

namespace App\Domains\Brand\Enums;

enum WritingSampleSourceType: string
{
    case Manual = 'manual';
    case LinkedinPost = 'linkedin_post';
    case ContentImport = 'content_import';
    case AgentOutput = 'agent_output';
    case Email = 'email';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Manual;
    }
}
