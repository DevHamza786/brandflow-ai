<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\GeneratedOutputSerializerContract;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Enums\GeneratedOutputStatus;

final class GeneratedOutputSerializer implements GeneratedOutputSerializerContract
{
    /** @var list<string> */
    private const METADATA_REDACT_KEYS = [
        'raw_prompt',
        'raw_response',
        'api_key',
        'session_ref',
    ];

    public function toArray(GeneratedOutputDto $dto): array
    {
        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'workflow_run_id' => $dto->workflowRunId,
            'agent_run_id' => $dto->agentRunId,
            'content_version_id' => $dto->contentVersionId,
            'type' => $dto->type->value,
            'provider' => $dto->provider,
            'model' => $dto->model,
            'prompt_version' => $dto->promptVersion,
            'status' => $dto->status->value,
            'input' => $dto->input->payload,
            'output' => $this->outputWhenTerminal($dto),
            'scores' => $dto->scores->toArray(),
            'metadata' => $this->redactMetadata($dto->metadata->toStorageArray()),
            'created_at' => $dto->createdAt?->toIso8601String(),
            'updated_at' => $dto->updatedAt?->toIso8601String(),
        ];
    }

    public function toSummary(GeneratedOutputDto $dto): array
    {
        return [
            'id' => $dto->id,
            'type' => $dto->type->value,
            'status' => $dto->status->value,
            'agent_run_id' => $dto->agentRunId,
            'workflow_run_id' => $dto->workflowRunId,
            'content_version_id' => $dto->contentVersionId,
            'provider' => $dto->provider,
            'model' => $dto->model,
            'scores' => [
                'overall' => $dto->scores->overall,
            ],
            'trace_id' => $dto->metadata->traceId,
            'created_at' => $dto->createdAt?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function outputWhenTerminal(GeneratedOutputDto $dto): ?array
    {
        if (! $dto->status->isTerminal()) {
            return null;
        }

        return $dto->output?->payload;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function redactMetadata(array $metadata): array
    {
        foreach (self::METADATA_REDACT_KEYS as $key) {
            unset($metadata[$key]);
        }

        return $metadata;
    }
}
