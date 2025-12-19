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
        // 1. Alias Middleware (Kode Lama Kamu)
        $middleware->alias([
            'protect.superadmin' => \App\Http\Middleware\ProtectSuperAdmin::class,
            'check.role' => \App\Http\Middleware\CheckUserRole::class,
        ]);

        // 2. MATIKAN CSRF KHUSUS WEBHOOK (Tambahan Baru)
        $middleware->validateCsrfTokens(except: [
            '/webhook/bank-result', // <--- Route Pintu Belakang untuk n8n
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();