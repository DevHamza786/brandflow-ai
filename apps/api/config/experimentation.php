<?php

declare(strict_types=1);

return [

    'min_samples_for_comparison' => (int) env('EXPERIMENT_MIN_SAMPLES', 30),

    'confidence_threshold' => (float) env('EXPERIMENT_CONFIDENCE_THRESHOLD', 0.90),

    'default_traffic_split' => [
        'control' => 0.5,
        'variant_a' => 0.5,
    ],

    'experiment_templates' => [
        'hook_ab' => [
            'type' => 'hook_ab',
            'name' => 'Hook A/B Test',
            'variants' => [
                ['key' => 'control', 'label' => 'Control hook', 'is_control' => true, 'weight' => 0.5, 'payload' => ['hook_style' => 'authority']],
                ['key' => 'variant_a', 'label' => 'Question-led hook', 'is_control' => false, 'weight' => 0.5, 'payload' => ['hook_style' => 'question_led']],
            ],
        ],
        'cta' => [
            'type' => 'cta',
            'name' => 'CTA Experiment',
            'variants' => [
                ['key' => 'control', 'label' => 'Soft CTA', 'is_control' => true, 'weight' => 0.5, 'payload' => ['cta_type' => 'soft']],
                ['key' => 'variant_a', 'label' => 'Direct CTA', 'is_control' => false, 'weight' => 0.5, 'payload' => ['cta_type' => 'direct']],
            ],
        ],
        'posting_time' => [
            'type' => 'posting_time',
            'name' => 'Posting Time Test',
            'variants' => [
                ['key' => 'control', 'label' => 'Morning slot', 'is_control' => true, 'weight' => 0.5, 'payload' => ['hour' => 9]],
                ['key' => 'variant_a', 'label' => 'Afternoon slot', 'is_control' => false, 'weight' => 0.5, 'payload' => ['hour' => 14]],
            ],
        ],
    ],

    'ml' => [
        'enabled' => (bool) env('EXPERIMENT_ML_ENABLED', false),
    ],

];
