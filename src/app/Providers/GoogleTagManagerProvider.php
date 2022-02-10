<?php

namespace App\Providers;

use App\Facades\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class GoogleTagManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        GoogleTagManagerFacade::macro('view', function (string $page, ?array $content = null) {
            $currency = Currency::getCurrentCurrency();
            GoogleTagManagerFacade::set(array_filter([
                'pageType' => $page,
                'user_type' => Auth::check() ? Auth::user()->usergroup_id : 'guest',
                'user_id' => Auth::id(),
                'site_price' => [
                    'name' => $currency->code,
                    'rate' => $currency->rate,
                ],
                'page_content' => $content,
                'event' => 'view_page',
            ]));
        });


    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
