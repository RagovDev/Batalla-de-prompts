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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Esto carga el middleware de sesión en el grupo 'api'
        $middleware->appendToGroup('api', [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // ======================================================================
        // Esto le dice a Laravel que NO revise el token CSRF en ninguna
        // ruta que empiece con 'api/'
        $middleware->validateCsrfTokens(except: [
            'api/*' 
        ]);
        // ======================================================================

        // Esto para saber si el usuario que se logea tiene rol de admin
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
