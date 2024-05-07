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

        $middleware->alias([
            'Owner' => App\Http\Middleware\ManagerOprasional::class,
            'MO' => App\Http\Middleware\ManagerOprasional::class,
            'admin' => App\Http\Middleware\Admin::class,
            'karyawan'=>App\Http\Middleware\Karyawan::class,
            'customer'=>App\Http\Middleware\Customer::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
