<?php

namespace App\Providers;

use App\Contracts\OrderServiceIntarface;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Observers\UserObserver;
use App\Services\OrderService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        OrderServiceIntarface::class => OrderService::class,
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
        Schema::defaultStringLength(191);

        Paginator::useBootstrap();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale(config('app.locale'));

        User::observe(UserObserver::class);
    }
}
