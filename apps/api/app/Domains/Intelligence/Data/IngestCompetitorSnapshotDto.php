<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Data;

use App\Domains\Intelligence\Enums\CompetitorSnapshotSource;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Manual / simulated ingest (no scrape).
 *
 * @param  array<string, mixed>  $payload  Normalized posts[] + optional profile
 * @param  array<string, mixed>  $metadata
 */
final class IngestCompetitorSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $competitorId,
        public readonly array $payload,
        public readonly ?CarbonInterface $capturedAt = null,
        public readonly CompetitorSnapshotSource $source = CompetitorSnapshotSource::ManualIngest,
        public readonly array $metadata = [],
    ) {
    }
}
