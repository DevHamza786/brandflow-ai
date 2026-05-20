<?php

declare(strict_types=1);

return [

    'min_confidence' => (float) env('AUTONOMOUS_MIN_CONFIDENCE', 0.65),

    'min_recommendation_score' => (int) env('AUTONOMOUS_MIN_RECOMMENDATION_SCORE', 50),

    'min_decision_score' => (int) env('AUTONOMOUS_MIN_DECISION_SCORE', 45),

    'default_mode' => env('AUTONOMOUS_DEFAULT_MODE', 'suggest'),

    'orchestration_batch_limit' => (int) env('AUTONOMOUS_BATCH_LIMIT', 25),

    'lock_ttl_seconds' => (int) env('AUTONOMOUS_LOCK_TTL_SECONDS', 120),

    'scheduler_enabled' => (bool) env('AUTONOMOUS_SCHEDULER_ENABLED', false),

];
