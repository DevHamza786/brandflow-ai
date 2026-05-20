<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use Carbon\Carbon;

/**
 * Posting cadence from competitor post timestamps.
 */
final class PostingFrequencyAnalyzer
{
    /**
     * @param  list<array<string, mixed>>  $posts
     * @return array<string, mixed>
     */
    public function analyze(array $posts, ?\DateTimeInterface $capturedAt = null): array
    {
        $dates = [];
        $hourBuckets = [];

        foreach ($posts as $post) {
            $at = $post['published_at'] ?? null;
            if ($at === null) {
                continue;
            }
            try {
                $dt = Carbon::parse($at);
            } catch (\Throwable) {
                continue;
            }
            $dates[] = $dt;
            $h = (int) $dt->format('G');
            $hourBuckets[$h] = ($hourBuckets[$h] ?? 0) + 1;
        }

        if ($dates === []) {
            return [
                'posts_per_week' => 0.0,
                'active_days' => 0,
                'hour_histogram' => [],
            ];
        }

        usort($dates, static fn ($a, $b) => $a <=> $b);
        $spanDays = max(1, $dates[0]->diffInDays($dates[count($dates) - 1]) + 1);
        $postsPerWeek = (count($dates) / $spanDays) * 7;

        ksort($hourBuckets);
        $histogram = [];
        foreach ($hourBuckets as $hour => $count) {
            $histogram[] = ['hour' => $hour, 'post_count' => $count];
        }

        return [
            'posts_per_week' => round($postsPerWeek, 2),
            'active_days' => count(array_unique(array_map(static fn ($d) => $d->format('Y-m-d'), $dates))),
            'first_post_at' => $dates[0]->toIso8601String(),
            'last_post_at' => $dates[count($dates) - 1]->toIso8601String(),
            'hour_histogram' => $histogram,
            'captured_at' => $capturedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
