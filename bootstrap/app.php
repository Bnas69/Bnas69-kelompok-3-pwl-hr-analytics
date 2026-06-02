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
        // Tambahkan middleware khusus bila dibutuhkan.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tambahkan handler exception khusus bila dibutuhkan.
    })
    ->create();
