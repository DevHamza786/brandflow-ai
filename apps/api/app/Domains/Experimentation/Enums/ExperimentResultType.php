<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Enums;

enum ExperimentResultType: string
{
    case Assignment = 'assignment';
    case Observation = 'observation';
    case Comparison = 'comparison';
}
