<?php

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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\AutoBackupMiddleware::class);
        $middleware->alias([
            'check.license' => \App\Http\Middleware\CheckLicense::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.or.cashier' => \App\Http\Middleware\AdminOrCashierMiddleware::class,
            'api.audit' => \App\Http\Middleware\ApiAuditLog::class,
            'tax.readonly' => \App\Http\Middleware\TaxDeviceReadOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
