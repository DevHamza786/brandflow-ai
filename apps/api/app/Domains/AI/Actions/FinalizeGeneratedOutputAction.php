<?php

declare(strict_types=1);

namespace App\Domains\AI\Actions;

use App\Domains\AI\Contracts\GeneratedOutputPersistenceContract;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputMetadataDto;
use App\Domains\AI\Data\GeneratedOutputPayloadDto;
use App\Domains\AI\Data\GeneratedOutputScoresDto;

/**
 * Completes or fails a generated output from queue / agent runners.
 */
final class FinalizeGeneratedOutputAction
{
    public function __construct(
        private readonly GeneratedOutputPersistenceContract $persistence,
    ) {
    }

    public function complete(
        string $workspaceId,
        string $generatedOutputId,
        GeneratedOutputPayloadDto $output,
        ?GeneratedOutputScoresDto $scores = null,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto {
        return $this->persistence->complete(
            $workspaceId,
            $generatedOutputId,
            $output,
            $scores,
            $metadataPatch,
        );
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public function fail(
        string $workspaceId,
        string $generatedOutputId,
        array $error,
        ?GeneratedOutputMetadataDto $metadataPatch = null,
    ): GeneratedOutputDto {
        return $this->persistence->fail(
            $workspaceId,
            $generatedOutputId,
            $error,
            $metadataPatch,
        );
    }
}
