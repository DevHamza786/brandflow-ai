<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Local dev: auto-create missing workspace rows (FK safety)
    |--------------------------------------------------------------------------
    | When true and APP_ENV=local, missing `workspaces` rows are upserted on demand
    | (e.g. before LinkedIn integration insert) so OAuth callbacks do not fail with 23503.
    */
    'workspace' => [
        'local_auto_bootstrap' => filter_var(
            env('PBOS_AUTO_BOOTSTRAP_LOCAL_WORKSPACE', '1'),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'dev_workspace_id' => env('PBOS_DEV_WORKSPACE_ID', '9bb59c64-347a-48b3-b010-e41b5cdc6f4d'),
];
