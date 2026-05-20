<?php

declare(strict_types=1);

return [

    'lookback_days' => (int) env('OPTIMIZATION_LOOKBACK_DAYS', 30),

    'comparison_days' => (int) env('OPTIMIZATION_COMPARISON_DAYS', 30),

    'min_samples_period' => (int) env('OPTIMIZATION_MIN_SAMPLES', 3),

    'min_uplift_pct' => (float) env('OPTIMIZATION_MIN_UPLIFT_PCT', 10.0),

    'min_score_to_persist' => (int) env('OPTIMIZATION_MIN_SCORE', 40),

    'max_snapshots_per_cycle' => (int) env('OPTIMIZATION_MAX_SNAPSHOTS', 20),

];
