<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Intelligence\Actions\CreateCompetitorAction;
use App\Domains\Intelligence\Actions\IngestCompetitorSnapshotAction;
use App\Domains\Intelligence\Data\CreateCompetitorDto;
use App\Domains\Intelligence\Data\IngestCompetitorSnapshotDto;
use App\Domains\Intelligence\Enums\CompetitorSnapshotSource;
use App\Domains\Intelligence\Services\CompetitorQueryService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestCompetitorSnapshotRequest;
use App\Http\Requests\Api\V1\StoreCompetitorRequest;
use App\Http\Resources\Api\V1\CompetitorResource;
use App\Http\Resources\Api\V1\CompetitorSnapshotResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompetitorsController extends Controller
{
    use RespondsWithApiEnvelope;

    public function index(Request $request, CompetitorQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $rows = $query->listCompetitors($workspaceId);

        return $this->success([
            'competitors' => CompetitorResource::collection($rows)->resolve($request),
        ]);
    }

    public function store(
        StoreCompetitorRequest $request,
        CreateCompetitorAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $validated = $request->validated();

        $dto = $action->execute(new CreateCompetitorDto(
            workspaceId: $workspaceId,
            linkedinUrl: (string) $validated['linkedin_url'],
            name: isset($validated['name']) ? (string) $validated['name'] : null,
            linkedinUrn: isset($validated['linkedin_urn']) ? (string) $validated['linkedin_urn'] : null,
            labels: is_array($validated['labels'] ?? null) ? $validated['labels'] : [],
            metadata: is_array($validated['metadata'] ?? null) ? $validated['metadata'] : [],
            scrapeCadenceHours: (int) ($validated['scrape_cadence_hours'] ?? 24),
        ));

        return $this->success(CompetitorResource::make($dto), 201);
    }

    public function show(
        string $competitorId,
        Request $request,
        CompetitorQueryService $query,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $report = $query->intelligenceReport($workspaceId, $competitorId);

        if ($report === null) {
            return $this->problem(404, 'competitor_not_found', 'Not found', 'Competitor not found.');
        }

        return $this->success($report->toArray());
    }

    public function ingestSnapshot(
        string $competitorId,
        IngestCompetitorSnapshotRequest $request,
        IngestCompetitorSnapshotAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $snapshot = $action->execute(new IngestCompetitorSnapshotDto(
            workspaceId: $workspaceId,
            competitorId: $competitorId,
            payload: $request->payload(),
            capturedAt: isset($request->validated()['captured_at'])
                ? \Carbon\Carbon::parse($request->validated()['captured_at'])
                : null,
            source: CompetitorSnapshotSource::ApiSimulate,
            metadata: is_array($request->validated()['metadata'] ?? null)
                ? $request->validated()['metadata']
                : [],
        ));

        return $this->success(CompetitorSnapshotResource::make($snapshot), 202);
    }
}
