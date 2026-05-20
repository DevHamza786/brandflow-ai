<?php

use App\Domains\Agents\Agents\HookAgent\Exceptions\HookAgentException;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookContentNotFoundException;
use App\Domains\AI\Exceptions\AiException;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://pbos.dev/problems/not-found',
                'title' => 'Resource Not Found',
                'status' => 404,
                'detail' => 'The requested resource does not exist.',
            ], 404, ['Content-Type' => 'application/problem+json']);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://pbos.dev/problems/validation-error',
                'title' => 'Validation Failed',
                'status' => 422,
                'detail' => 'The request payload failed validation.',
                'errors' => $e->errors(),
            ], 422, ['Content-Type' => 'application/problem+json']);
        });

        $exceptions->render(function (HookContentNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://pbos.dev/problems/content-not-found',
                'title' => 'Content Not Found',
                'status' => 404,
                'detail' => $e->getMessage(),
            ], 404, ['Content-Type' => 'application/problem+json']);
        });

        $exceptions->render(function (HookAgentException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://pbos.dev/problems/hook-agent-error',
                'title' => 'Hook Agent Error',
                'status' => 422,
                'detail' => $e->getMessage(),
                'context' => $e->context,
            ], 422, ['Content-Type' => 'application/problem+json']);
        });

        $exceptions->render(function (LinkedInOAuthException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://pbos.dev/problems/linkedin-oauth-error',
                'title' => 'LinkedIn OAuth Error',
                'status' => 422,
                'detail' => $e->getMessage(),
                'context' => $e->context,
            ], 422, ['Content-Type' => 'application/problem+json']);
        });

        $exceptions->render(function (AiException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = str_contains(strtolower($e->getMessage()), 'circuit') ? 503 : 502;

            return response()->json([
                'type' => 'https://pbos.dev/problems/ai-provider-error',
                'title' => 'AI Provider Error',
                'status' => $status,
                'detail' => $e->getMessage(),
                'context' => $e->context,
            ], $status, ['Content-Type' => 'application/problem+json']);
        });
    })->create();
