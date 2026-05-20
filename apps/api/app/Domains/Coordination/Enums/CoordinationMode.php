<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationMode: string
{
    case Sequential = 'sequential';
    case Parallel = 'parallel';
    case StrategistLed = 'strategist_led';
}
