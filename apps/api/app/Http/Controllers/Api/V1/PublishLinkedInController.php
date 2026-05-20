<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Schedule\Actions\PublishToLinkedInAction;
use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PublishLinkedInRequest;
use App\Http\Resources\Api\V1\ScheduledPostResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Queues a LinkedIn publish (worker + provider). Thin orchestration only.
 */
final class PublishLinkedInController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly PublishToLinkedInAction $publishToLinkedIn,
    ) {
    }

    public function store(PublishLinkedInRequest $request): JsonResponse
    {
        $scheduledFor = $request->filled('scheduled_for')
            ? Carbon::parse((string) $request->validated('scheduled_for'))
            : Carbon::now();

        $dto = new CreateScheduledPostDto(
            workspaceId: $request->workspaceId(),
            linkedinIntegrationId: (string) $request->validated('linkedin_integration_id'),
            scheduledFor: $scheduledFor,
            content: $request->validated('content'),
            generatedOutputId: $request->validated('generated_output_id'),
            metadata: [
                'source' => 'api',
            ],
        );

        $scheduled = $this->publishToLinkedIn->execute($dto);

        return $this->success(ScheduledPostResource::make($scheduled), 202);
    }
}
