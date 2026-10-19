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
    ->withMiddleware(function (Middleware $middleware) {
        // Records every successful write action to activity_logs. Runs after
        // the response and swallows its own errors, so it cannot break a page.
        $middleware->web(append: [
            \App\Http\Middleware\LogActivity::class,
            // Ends an open session the moment an account is deactivated.
            // Runs before any route's can: gate, so it also covers the three
            // ungated routes (dashboard and the change-password form).
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
