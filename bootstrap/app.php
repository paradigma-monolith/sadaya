<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->redirectTo(
            guests: '/login',
            users: '/',
        );
        $middleware->validateCsrfTokens(except: [
            'chat/*',
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \Sentry\Laravel\Integration::handles($exceptions);

        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            $status = $response->getStatusCode();

            if (! in_array($status, [403, 404, 419, 500, 503], true)) {
                return $response;
            }

            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return $response;
            }

            if ($status === 500 && app()->environment(['local', 'testing']) && config('app.debug') && ! $request->header('X-Inertia')) {
                return $response;
            }

            return \Inertia\Inertia::render('Error', [
                'status' => $status,
                'message' => app()->environment(['local', 'testing']) ? $exception->getMessage() : null,
                'isAuthenticated' => (bool) $request->user(),
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
