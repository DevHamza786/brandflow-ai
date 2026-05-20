<?php

declare(strict_types=1);

use App\Http\Controllers\Integrations\LinkedInOAuthCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/integrations/linkedin/callback',
    LinkedInOAuthCallbackController::class,
)->name('integrations.linkedin.callback');
