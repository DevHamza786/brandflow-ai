<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Enums;

enum RecommendationStatus: string
{
    case Active = 'active';
    case Superseded = 'superseded';
    case Dismissed = 'dismissed';
    case Expired = 'expired';
}
