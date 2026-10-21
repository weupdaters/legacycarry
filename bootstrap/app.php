<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Auto-purge stale compiled route and config cache files so new routes load immediately
$staleCacheFiles = [
    __DIR__ . '/cache/routes-v7.php',
    __DIR__ . '/cache/routes.php',
    __DIR__ . '/cache/config.php',
];
foreach ($staleCacheFiles as $staleFile) {
    if (file_exists($staleFile)) {
        @unlink($staleFile);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
           'isSuperAdmin' => \App\Http\Middleware\IsSuperAdmin::class,
           'isUser' => \App\Http\Middleware\UserMiddleware::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
