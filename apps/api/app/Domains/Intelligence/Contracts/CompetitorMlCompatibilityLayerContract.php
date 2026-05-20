<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

/**
 * Future embeddings / RAG / predictive models on competitor snapshots.
 */
interface CompetitorMlCompatibilityLayerContract
{
    /**
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    public function buildFeatures(array $analytics, array $payload): array;
}
