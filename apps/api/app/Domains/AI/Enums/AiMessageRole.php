<?php

declare(strict_types=1);

namespace App\Domains\AI\Enums;

enum AiMessageRole: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
}
