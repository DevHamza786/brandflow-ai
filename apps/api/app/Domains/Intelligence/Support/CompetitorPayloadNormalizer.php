<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Support;

/**
 * Canonical post rows from ingest payload (scrape-ready shape).
 */
final class CompetitorPayloadNormalizer
{
    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    public function posts(array $payload): array
    {
        $posts = $payload['posts'] ?? [];
        if (! is_array($posts)) {
            return [];
        }

        $out = [];
        foreach ($posts as $post) {
            if (! is_array($post)) {
                continue;
            }
            $hook = (string) ($post['hook_text'] ?? $post['hook'] ?? $post['opening_line'] ?? '');
            $out[] = [
                'post_id' => (string) ($post['post_id'] ?? $post['id'] ?? ''),
                'published_at' => $post['published_at'] ?? $post['posted_at'] ?? null,
                'hook_text' => $hook,
                'body_preview' => (string) ($post['body_preview'] ?? $post['text'] ?? ''),
                'impressions' => (int) ($post['impressions'] ?? 0),
                'likes' => (int) ($post['likes'] ?? 0),
                'comments' => (int) ($post['comments'] ?? 0),
                'reposts' => (int) ($post['reposts'] ?? $post['shares'] ?? 0),
                'saves' => (int) ($post['saves'] ?? 0),
                'cta_text' => isset($post['cta_text']) ? (string) $post['cta_text'] : null,
            ];
        }

        return $out;
    }

    public function canonicalHash(array $payload): string
    {
        $json = json_encode($this->posts($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $json);
    }

    public function engagementRate(int $impressions, int $likes, int $comments, int $reposts, int $saves): float
    {
        if ($impressions <= 0) {
            $interactions = $likes + $comments + $reposts + $saves;

            return $interactions > 0 ? min(1.0, $interactions / 1000) : 0.0;
        }

        return ($likes + $comments + $reposts + $saves) / $impressions;
    }
}
