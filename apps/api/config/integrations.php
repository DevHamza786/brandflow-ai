<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth state TTL (CSRF protection)
    |--------------------------------------------------------------------------
    */
    'oauth_state_ttl_seconds' => (int) env('INTEGRATIONS_OAUTH_STATE_TTL', 600),

    /*
    |--------------------------------------------------------------------------
    | Token refresh scheduling (future scheduler / Horizon)
    |--------------------------------------------------------------------------
    */
    'token_refresh_lead_seconds' => (int) env('INTEGRATIONS_TOKEN_REFRESH_LEAD', 3600),

    'max_refresh_attempts' => (int) env('INTEGRATIONS_MAX_REFRESH_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | LinkedIn OAuth 2.0 / OpenID
    |--------------------------------------------------------------------------
    */
    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect_uri' => env('LINKEDIN_REDIRECT_URI', env('APP_URL').'/integrations/linkedin/callback'),

        'authorize_url' => env('LINKEDIN_AUTHORIZE_URL', 'https://www.linkedin.com/oauth/v2/authorization'),
        'token_url' => env('LINKEDIN_TOKEN_URL', 'https://www.linkedin.com/oauth/v2/accessToken'),
        'userinfo_url' => env('LINKEDIN_USERINFO_URL', 'https://api.linkedin.com/v2/userinfo'),

        'scopes' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'LINKEDIN_SCOPES',
                'openid,profile,email',
            )),
        ))),

        'http_timeout_seconds' => (int) env('LINKEDIN_HTTP_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | LinkedIn REST authoring (UGC) — versioned separately from OAuth
    |--------------------------------------------------------------------------
    */
    'linkedin_publishing' => [
        'api_base' => env('LINKEDIN_API_BASE', 'https://api.linkedin.com'),
        'ugc_path' => env('LINKEDIN_UGC_PATH', '/v2/ugcPosts'),
        'api_version' => env('LINKEDIN_API_VERSION', '202404'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Post-connect redirect (SPA / dashboard)
    |--------------------------------------------------------------------------
    */
    'default_success_redirect' => env(
        'INTEGRATIONS_SUCCESS_REDIRECT',
        env('FRONTEND_URL', 'http://localhost:5173').'/settings/integrations?linkedin=connected',
    ),

    'default_error_redirect' => env(
        'INTEGRATIONS_ERROR_REDIRECT',
        env('FRONTEND_URL', 'http://localhost:5173').'/settings/integrations?linkedin=error',
    ),

];
