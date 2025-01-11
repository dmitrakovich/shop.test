<?php

namespace App\Providers;

use App\Facades\Currency;
use App\Facades\Device;
use App\Models\Data\UserData;
use App\Models\Guest;
use App\View\Creators\UserDataCreator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
            $userData = new UserData(Auth::check() ? Auth::user()->toArray() : Guest::getData());
            $userData->setExternalIds([Device::id(), Auth::id()]);
            GoogleTagManagerFacade::push(array_filter([
                'pageType' => $page,
                'user_type' => Auth::check() ? Auth::user()->group_id : 'guest',
                'user_id' => Auth::id(),
                'user_data' => $userData->normalizeForGtm(),
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

        View::creator('googletagmanager::head', UserDataCreator::class);
    }
}
