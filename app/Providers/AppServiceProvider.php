<?php

namespace App\Providers;

use App\Observers\UserObserver;
use App\Repositories\CategoryRepository;
use App\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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

        User::observe(UserObserver::class);

        View::share([
            'categories' => (new CategoryRepository)->getAll()
        ]);
    }
}
