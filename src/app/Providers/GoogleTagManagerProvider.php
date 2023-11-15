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
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        GoogleTagManagerFacade::macro('view', function (string $page, ?array $content = null) {
            $currency = Currency::getCurrentCurrency();
            $userData = Auth::check() ? Auth::user() : Guest::getData();
            GoogleTagManagerFacade::push(array_filter([
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

        GoogleTagManagerFacade::macro('ecommerce', function (string $action, array $ecommerce) {
            $ecommerce['currencyCode'] = 'USD';
            GoogleTagManagerFacade::push([
                'ecommerce' => $ecommerce,
                'event' => 'ecom_event',
                'event_label' => $action,
                'event_category' => 'ecommerce',
                'event_action' => $action,
            ]);
        });

        GoogleTagManagerFacade::macro('ecommerceFlash', function (string $action, array $ecommerce) {
            $ecommerce['currencyCode'] = 'USD';
            GoogleTagManagerFacade::flash([
                'ecommerce' => $ecommerce,
                'event' => 'ecom_event',
                'event_label' => $action,
                'event_category' => 'ecommerce',
                'event_action' => $action,
            ]);
        });

        GoogleTagManagerFacade::macro('user', function (string $action) {
            GoogleTagManagerFacade::flash([
                'event' => 'user_event',
                'event_label' => $action,
                'event_category' => 'user',
                'event_action' => $action,
            ]);
        });
    }
}
