<?php

declare(strict_types=1);

/**
 * PBOS queue names and worker defaults.
 *
 * @see docs/AGENTS.md §7.1 Queue assignment
 */
return [

    'names' => [
        'critical',
        'ai',
        'scrape',
        'analytics',
        'default',
    ],

    'timeouts' => [
        'critical' => 90,
        'ai' => 120,
        'scrape' => 180,
        'analytics' => 90,
        'default' => 60,
    ],

];
