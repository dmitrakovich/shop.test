<?php

use App\Enums\Cookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->validateSignatures(['utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term'])
            ->preventRequestsDuringMaintenance(['/opcache-api/*'])
            ->encryptCookies(['utm', Cookie::YANDEX_ID->value, Cookie::GOOGLE_ID->value])
            ->throttleApi(redis: true);

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
