<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GeneratedOutputRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateGeneratedOutputDto $dto): GeneratedOutputDto;

    /**
     * Idempotent write for async agent retries — updates existing row when present.
     */
    public function upsertForAgentRun(CreateGeneratedOutputDto $dto): GeneratedOutputDto;

    /**
     * Idempotent upsert — prefers workflow_run_id when set, else agent_run_id.
     */
    public function upsertForExecution(CreateGeneratedOutputDto $dto): GeneratedOutputDto;

    public function findByWorkflowRun(
        string $workspaceId,
        string $workflowRunId,
        GeneratedOutputType $type,
    ): ?GeneratedOutputDto;

    public function findById(string $workspaceId, string $id): ?GeneratedOutputDto;

    public function findLatestByAgentRun(
        string $workspaceId,
        string $agentRunId,
        ?GeneratedOutputType $type = null,
    ): ?GeneratedOutputDto;

    /**
     * @return list<GeneratedOutputDto>
     */
    public function listByWorkflowRun(string $workspaceId, string $workflowRunId): array;

    /**
     * @return list<GeneratedOutputDto>
     */
    public function listByContentVersion(
        string $workspaceId,
        string $contentVersionId,
        ?GeneratedOutputType $type = null,
        int $limit = 20,
    ): array;

    /**
     * @param  array{
     *     type?: GeneratedOutputType,
     *     status?: GeneratedOutputStatus,
     *     provider?: string,
     *     from?: string,
     *     to?: string,
     * }  $filters
     */
    public function paginateForWorkspace(
        string $workspaceId,
        array $filters = [],
        int $perPage = 25,
    ): LengthAwarePaginator;

    public function updateStatus(
        string $workspaceId,
        string $id,
        GeneratedOutputStatus $status,
        ?array $output = null,
        ?array $scores = null,
        ?array $metadataPatch = null,
    ): GeneratedOutputDto;

    public function markSuperseded(string $workspaceId, string $id): GeneratedOutputDto;

    public function linkWorkflowRun(
        string $workspaceId,
        string $id,
        string $workflowRunId,
    ): GeneratedOutputDto;
}
