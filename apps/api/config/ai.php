<?php

declare(strict_types=1);

/**
 * PBOS AI layer configuration.
 *
 * @see docs/PROJECT_ARCHITECTURE.md §5 AI Pipeline
 * @see docs/AGENTS.md §8 Prompt Engineering
 */
return [

    'use_null_gateway' => (bool) env('AI_USE_NULL_GATEWAY', false),

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    'fallback_provider' => env('AI_FALLBACK_PROVIDER', 'gemini'),

    'enable_fallback' => (bool) env('AI_ENABLE_FALLBACK', true),

    'trace_id_header' => 'X-PBOS-Trace-Id',

    /*
    |--------------------------------------------------------------------------
    | Retry policy (gateway-level; rate limits use longer backoff)
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => (int) env('AI_RETRY_MAX_ATTEMPTS', 3),
        'backoff_ms' => [500, 2000, 5000],
        'retry_on' => [
            'rate_limit',
            'timeout',
            'server_error',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt templates (filesystem; DB registry added later)
    |--------------------------------------------------------------------------
    */
    'prompts' => [
        'base_path' => resource_path('prompts'),
        'default_version' => env('AI_PROMPT_DEFAULT_VERSION', 'v1'),
        'view_namespace' => 'prompts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider definitions
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 120),
            'embeddings_model' => env('OPENAI_EMBEDDINGS_MODEL', 'text-embedding-3-small'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 120),
            'embeddings_model' => env('GEMINI_EMBEDDINGS_MODEL', 'text-embedding-004'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-2.0-flash'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Memory injection (RAG context formatting)
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'system_preamble' => 'Use the following brand memory chunks when relevant. Cite as [mem:{id}].',
        'max_chunks' => (int) env('AI_MEMORY_MAX_CHUNKS', 10),
    ],

];
