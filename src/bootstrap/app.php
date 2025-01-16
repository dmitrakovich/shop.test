<?php

use App\Enums\Cookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([__DIR__ . '/../routes/console.php'])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->validateSignatures(['utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term'])
            ->preventRequestsDuringMaintenance(['/opcache-api/*'])
            ->encryptCookies(['utm', Cookie::YANDEX_ID->value, Cookie::GOOGLE_ID->value])
            ->throttleApi(redis: true);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\DeviceDetect::class,
            \App\Http\Middleware\MigrateCartToDevice::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Spatie\GoogleTagManager\GoogleTagManagerMiddleware::class,
            \App\Http\Middleware\ViewMiddleware::class,
        ]);

        $middleware->alias([
            'captcha' => \App\Http\Middleware\Captcha::class,
            'device.auth' => \App\Http\Middleware\DeviceAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })
    ->withEvents(discover: false)
    ->create();
