<?php

namespace App\Providers;

use App\Contracts\OrderServiceInterface;
use App\Services\OrderService;
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
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale(config('app.locale'));

        if ($this->app->environment('local')) {
            $this->app['config']['filesystems.disks.public.url'] = 'https://barocco.by/media';
        }
    }
}
