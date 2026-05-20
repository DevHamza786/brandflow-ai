<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\AnalyticsEventRepositoryContract;
use App\Domains\Analytics\Data\AnalyticsEventDto;
use App\Domains\Analytics\Data\CreateAnalyticsEventDto;
use Carbon\CarbonInterface;

/**
 * Append-only event stream with idempotency support (event-driven ingestion boundary).
 */
final class AnalyticsEventIngestionService
{
    public function __construct(
        private readonly AnalyticsEventRepositoryContract $events,
        private readonly AnalyticsExecutionLogger $logger,
    ) {
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function ingest(
        string $workspaceId,
        string $eventType,
        ?string $entityType = null,
        ?string $entityId = null,
        array $properties = [],
        ?CarbonInterface $occurredAt = null,
        ?string $idempotencyKey = null,
    ): AnalyticsEventDto {
        $dto = $this->events->append(new CreateAnalyticsEventDto(
            workspaceId: $workspaceId,
            eventType: $eventType,
            entityType: $entityType,
            entityId: $entityId,
            properties: $properties,
            occurredAt: $occurredAt,
            idempotencyKey: $idempotencyKey,
        ));

        $this->logger->info('event.ingested', [
            'workspace_id' => $workspaceId,
            'event_type' => $eventType,
            'event_id' => $dto->id,
        ]);

        return $dto;
    }
}
