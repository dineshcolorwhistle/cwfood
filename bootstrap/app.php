<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\{VerifyCsrfToken,AutoLogout,SetSideMenuContext,CheckSubscriptionValidity,VerifyCognitoIdToken};

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middleware  
        $middleware->web([
            AutoLogout::class,
            SetSideMenuContext::class,
            CheckSubscriptionValidity::class,
        ]);

        // Route middleware aliases   
        $middleware->alias([
            'cognito.id' => VerifyCognitoIdToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

    // 👇 bind your custom kernel before returning
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );

return $app;
