<?php

namespace App\Providers;

use App\Contracts\OrderServiceInterface;
use App\Notifications\ChannelManagerWithLimits;
use App\Services\OrderService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        OrderServiceInterface::class => OrderService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ChannelManager::class, function ($app) {
            return new ChannelManagerWithLimits($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale(config('app.locale'));

        if ($this->app->environment('local')) {
            $this->app['config']['filesystems.disks.public.url'] = 'https://barocco.by/media';
        }
    }
}
