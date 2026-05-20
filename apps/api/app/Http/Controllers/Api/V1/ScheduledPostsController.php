<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Resources\Api\V1\ScheduledPostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scheduled / published LinkedIn posts — read-only list for UI activity.
 */
final class ScheduledPostsController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly ScheduledPostRepositoryContract $scheduledPosts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $limit = (int) $request->query('limit', 50);
        $rows = $this->scheduledPosts->listRecentForWorkspace($workspaceId, $limit);

        $inFlight = false;
        foreach ($rows as $dto) {
            if (in_array($dto->status->value, ['queued', 'scheduled', 'publishing'], true)) {
                $inFlight = true;
                break;
            }
        }

        return $this->success([
            'scheduled_posts' => ScheduledPostResource::collection($rows)->resolve(),
            'in_flight' => $inFlight,
        ]);
    }

    public function show(Request $request, string $scheduledPostId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $row = $this->scheduledPosts->findById($workspaceId, $scheduledPostId);

        if ($row === null) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/not-found',
                title: 'Scheduled Post Not Found',
                detail: 'No scheduled post with this id for your workspace.',
            );
        }

        return $this->success(ScheduledPostResource::make($row));
    }
}
