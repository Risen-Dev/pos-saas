<?php

use App\Http\Middleware\CheckTenant;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsManagerOrAdmin;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- FIX 1: Pastikan rute API didaftarkan di sini
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [PreventBackHistory::class]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'manager' => EnsureUserIsManagerOrAdmin::class,
            'check.tenant' => CheckTenant::class,
        ]);

        $middleware->validateCsrfTokens(except: ['midtrans/callback']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
