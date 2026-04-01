<?php

use App\Http\Middleware\EnsureNotBanned;
use App\Http\Middleware\EnsureNotGhost;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            UpdateLastSeen::class,
        ]);

        $middleware->alias([
            'role'       => EnsureRole::class,
            'not-ghost'  => EnsureNotGhost::class,
            'not-banned' => EnsureNotBanned::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();
