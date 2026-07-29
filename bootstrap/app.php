<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Restores CodeIgniter's case-insensitive routing. Must run before the
        // router, so it goes on the global stack.
        $middleware->prepend(App\Http\Middleware\NormalizeLegacyUrl::class);

        $middleware->alias([
            'admin' => App\Http\Middleware\EnsureAdmin::class,
            'worker' => App\Http\Middleware\EnsureWorker::class,
            'client' => App\Http\Middleware\EnsureClient::class,
        ]);

        // Unauthenticated browser requests to the admin area belong on the
        // admin login page, not the worker one.
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('admin*') ? url('admin') : url('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
