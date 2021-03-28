<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Category;
use App\Models\InfoPage;
use Illuminate\Support\Carbon;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale(config('app.locale'));

        User::observe(UserObserver::class);

        View::share([
            'categories' => Category::where('parent_id', 1)->get(['id', 'slug', 'title']),
            'infoPagesMenu' => InfoPage::getMenu()
        ]);
    }
}
