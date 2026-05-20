<?php

declare(strict_types=1);

return [

    'min_posts_for_patterns' => (int) env('INTEL_MIN_POSTS_FOR_PATTERNS', 3),

    'min_samples_style_uplift' => (int) env('INTEL_MIN_SAMPLES_STYLE', 3),

    'min_style_uplift_pct' => (float) env('INTEL_MIN_STYLE_UPLIFT_PCT', 15.0),

    'benchmark_lookback_days' => (int) env('INTEL_BENCHMARK_LOOKBACK_DAYS', 90),

    'trend_snapshots_min' => 2,

];
