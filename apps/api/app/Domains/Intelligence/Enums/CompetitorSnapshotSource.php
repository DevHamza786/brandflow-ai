<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum CompetitorSnapshotSource: string
{
    case ManualIngest = 'manual_ingest';
    case ApiSimulate = 'api_simulate';
    case Scrape = 'scrape';
}
