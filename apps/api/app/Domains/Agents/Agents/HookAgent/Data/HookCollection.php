<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Full Hook Lab output: primary score + ranked variants.
 */
final class HookCollection extends DataTransferObject
{
    /**
     * @param  list<HookVariant>  $variants
     */
    public function __construct(
        public readonly HookResult $primary,
        public readonly array $variants = [],
        public readonly ?string $traceId = null,
        public readonly ?string $model = null,
        public readonly ?string $experimentId = null,
    ) {
    }

    /**
     * @return list<HookVariant>
     */
    public function topVariants(int $limit = 3): array
    {
        $sorted = $this->variants;
        usort($sorted, static fn (HookVariant $a, HookVariant $b): int => $b->overall <=> $a->overall);

        return array_slice($sorted, 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAgentOutput(): array
    {
        return [
            'overall' => $this->primary->overall,
            'dimensions' => $this->primary->dimensions->toArray(),
            'suggestions' => $this->primary->suggestions,
            'variants' => array_map(static fn (HookVariant $v) => $v->toArray(), $this->variants),
            'trace_id' => $this->traceId,
            'model' => $this->model,
            'experiment_id' => $this->experimentId,
        ];
    }

    /**
     * Analytics-friendly payload for HookScored event / future analytics_events.
     *
     * @return array<string, mixed>
     */
    public function toAnalyticsPayload(string $contentVersionId, string $agentRunId): array
    {
        return [
            'content_version_id' => $contentVersionId,
            'agent_run_id' => $agentRunId,
            'overall_score' => $this->primary->overall,
            'variant_count' => count($this->variants),
            'top_variant_score' => $this->variants[0]->overall ?? null,
            'experiment_id' => $this->experimentId,
            'trace_id' => $this->traceId,
            'model' => $this->model,
        ];
    }
}
