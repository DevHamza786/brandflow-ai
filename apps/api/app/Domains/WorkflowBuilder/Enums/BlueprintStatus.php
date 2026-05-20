<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Enums;

enum BlueprintStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
