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
        // Reverse proxy (SSL termination) -> app over HTTP; trust only its backend-facing IP
        $middleware->trustProxies(at: ['10.100.1.135']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
