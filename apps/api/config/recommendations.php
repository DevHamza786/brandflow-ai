<?php

declare(strict_types=1);

return [

    /** Days of snapshots to analyze per generation run. */
    'lookback_days' => (int) env('RECOMMENDATIONS_LOOKBACK_DAYS', 90),

    'max_snapshots' => (int) env('RECOMMENDATIONS_MAX_SNAPSHOTS', 500),

    'max_persisted' => (int) env('RECOMMENDATIONS_MAX_PERSISTED', 50),

    /** Minimum posts per hook-style bucket to emit a style correlation. */
    'min_samples_style' => (int) env('RECOMMENDATIONS_MIN_SAMPLES_STYLE', 3),

    /** Minimum uplift vs workspace baseline (percent points) for style insights. */
    'min_uplift_pct' => (float) env('RECOMMENDATIONS_MIN_UPLIFT_PCT', 12.0),

    /** Minimum hour-of-day samples for posting-time advice. */
    'min_samples_posting_hour' => 2,

    /** Normalized engagement below this percentile band → weak performer. */
    'weak_performer_percentile' => 25,

    /** Minimum confidence score (0–100) to persist. */
    'min_score_to_persist' => (int) env('RECOMMENDATIONS_MIN_SCORE', 35),

];
