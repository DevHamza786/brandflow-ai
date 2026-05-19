<?php

declare(strict_types=1);

/**
 * Registered PBOS agents.
 *
 * @see docs/AGENTS.md §4 Agent Catalog
 *
 * @return array{
 *     agents: array<string, array{
 *         class: class-string|null,
 *         queue: string,
 *         timeout: int,
 *         description: string,
 *         primary_domain: string,
 *     }>
 * }
 */
return [

    'agents' => [

        'hook' => [
            'class' => \App\Domains\Agents\Agents\HookAgent\HookAgent::class,
            'queue' => 'ai',
            'timeout' => 120,
            'description' => 'Score and generate opening-line variants (Hook Lab).',
            'primary_domain' => 'Content',
        ],

        'profile' => [
            'class' => null, // App\Domains\Agents\Agents\ProfileAgent\ProfileAgent::class
            'queue' => 'ai',
            'timeout' => 120,
            'description' => 'Analyze and optimize LinkedIn profile sections.',
            'primary_domain' => 'Brand',
        ],

        'analytics' => [
            'class' => null, // App\Domains\Agents\Agents\AnalyticsAgent\AnalyticsAgent::class
            'queue' => 'analytics',
            'timeout' => 90,
            'description' => 'Generate insights and recommendations from rollups.',
            'primary_domain' => 'Analytics',
        ],

        'competitor' => [
            'class' => null, // App\Domains\Agents\Agents\CompetitorAgent\CompetitorAgent::class
            'queue' => 'ai',
            'timeout' => 120,
            'description' => 'Diff competitor snapshots and raise strategic alerts.',
            'primary_domain' => 'Intelligence',
        ],

        'reply' => [
            'class' => null, // App\Domains\Agents\Agents\ReplyAgent\ReplyAgent::class
            'queue' => 'ai',
            'timeout' => 120,
            'description' => 'Draft comment replies aligned with brand voice.',
            'primary_domain' => 'Content',
        ],

        'carousel' => [
            'class' => null, // App\Domains\Agents\Agents\CarouselAgent\CarouselAgent::class
            'queue' => 'ai',
            'timeout' => 120,
            'description' => 'Generate multi-slide carousel structure and copy.',
            'primary_domain' => 'Content',
        ],

    ],

];
