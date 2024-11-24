<?php

use App\Enums\Cookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->validateSignatures(['utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term'])
            ->preventRequestsDuringMaintenance(['/opcache-api/*'])
            ->encryptCookies(['utm', Cookie::YANDEX_ID->value, Cookie::GOOGLE_ID->value]);

        $middleware->web(append: [
            \App\Http\Middleware\DeviceDetect::class,
            \Spatie\GoogleTagManager\GoogleTagManagerMiddleware::class,
            \App\Http\Middleware\ViewMiddleware::class,
        ]);

        $middleware->alias([
            'captcha' => \App\Http\Middleware\Captcha::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })
    ->withEvents(discover: false)
    ->create();
