<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Agents\Data\AgentRunResultsDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Stable polling contract — keys are always present; values vary by status.
 *
 * @property AgentRunResultsDto $resource
 */
final class AgentRunResultsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AgentRunResultsDto $results */
        $results = $this->resource;

        return [
            'status' => $results->status,
            'outputs' => $results->outputs,
            'scores' => $this->emptyObjectIfEmpty($results->scores),
            'metadata' => $this->emptyObjectIfEmpty($results->metadata),
            'variants' => $results->variants,
            'dimensions' => $this->emptyObjectIfEmpty($results->dimensions),
            'suggestions' => $results->suggestions,
            'error' => $results->error,
            'timestamps' => $results->timestamps,
        ];
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>|\stdClass
     */
    private function emptyObjectIfEmpty(array $value): array|\stdClass
    {
        return $value === [] ? new \stdClass : $value;
    }
}
