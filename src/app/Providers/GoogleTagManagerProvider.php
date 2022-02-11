<?php

namespace App\Providers;

use App\Facades\Currency;
use App\Models\Guest;
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
            $userData = Auth::check() ?  Auth::user() : Guest::getData();
            GoogleTagManagerFacade::set(array_filter([
                'pageType' => $page,
                'user_type' => Auth::check() ? Auth::user()->usergroup_id : 'guest',
                'user_id' => Auth::id(),
                'user_data' => array_filter([
                    'fn' => $userData['first_name'] ?? null,
                    'ln' => $userData['last_name'] ?? null,
                    'em' => $userData['email'] ?? null,
                    'ph' => $userData['phone'] ?? null,
                ]),
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
