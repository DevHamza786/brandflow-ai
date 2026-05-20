<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Enums;

enum ExperimentStatus: string
{
    case Draft = 'draft';
    case Running = 'running';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';
}
