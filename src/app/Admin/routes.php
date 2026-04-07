<?php

use App\Admin\Controllers\Analytics;
use App\Admin\Controllers\Automation;
use App\Admin\Controllers\Bookkeeping;
use App\Admin\Controllers\CityController;
use App\Admin\Controllers\Config;
use App\Admin\Controllers\Debug\CacheController;
use App\Admin\Controllers\Departures;
use App\Admin\Controllers\DocController;
use App\Admin\Controllers\Forms;
use App\Admin\Controllers\InfoPageController;
use App\Admin\Controllers\Logs;
use App\Admin\Controllers\MediaController;
use App\Admin\Controllers\Offline\DisplacementController;
use App\Admin\Controllers\OrderCommentController;
use App\Admin\Controllers\OrderController as AdminOrderController;
use App\Admin\Controllers\OrderItemController;
use App\Admin\Controllers\Orders\OfflineOrderController;
use App\Admin\Controllers\OrdersDistribution\SettingsController;
use App\Admin\Controllers\OrdersDistribution\StatisticController;
use App\Admin\Controllers\ProductAttributes\BrandController;
use App\Admin\Controllers\ProductAttributes\CategoryController;
use App\Admin\Controllers\ProductAttributes\CollectionController;
use App\Admin\Controllers\ProductAttributes\ColorController;
use App\Admin\Controllers\ProductAttributes\CountryOfOriginController;
use App\Admin\Controllers\ProductAttributes\FabricController;
use App\Admin\Controllers\ProductAttributes\HeelHeightController;
use App\Admin\Controllers\ProductAttributes\ManufacturerController;
use App\Admin\Controllers\ProductAttributes\SeasonController;
use App\Admin\Controllers\ProductAttributes\StyleController;
use App\Admin\Controllers\ProductAttributes\TagController;
use App\Admin\Controllers\ProductAttributes\TagGroupController;
use App\Admin\Controllers\RatingController;
use App\Admin\Controllers\Seo\SeoLinkController;
use App\Admin\Controllers\StockController;
use App\Admin\Controllers\Users\GroupController;
use App\Admin\Controllers\Users\UserController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\Shop\OrderController;
use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix' => 'old-admin',
    'as' => 'admin.',
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->resource('info-pages', InfoPageController::class);

    $router->group(['prefix' => 'orders', 'as' => 'orders.'], function (Router $router) {
        $router->resource('offline', OfflineOrderController::class);
    });
    // todo: move to orders
    $router->resource('orders', AdminOrderController::class);
    $router->resource('order-items', OrderItemController::class);
    // $router->resource('order-comments', OrderCommentController::class); // todo: maybe excess
    $router->get('orders/{order}/process', [AdminOrderController::class, 'process'])->name('orders.process');
    $router->get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    $router->post('orders/add-user-by-phone', [AdminOrderController::class, 'addUserByPhone']);
    $router->post('orders/change-user-by-phone', [AdminOrderController::class, 'changeUserByPhone']);
    $router->post('orders/update-user-address', [AdminOrderController::class, 'updateUserAddress']);
    $router->post('orders/add-order-comment', [AdminOrderController::class, 'addOrderComment']);

    $router->group(['prefix' => 'users'], function (Router $router) {
        $router->resource('users', UserController::class);
        $router->resource('groups', GroupController::class);
    });

    $router->group(['prefix' => 'product-attributes'], function (Router $router) {
        $router->resource('categories', CategoryController::class);
        $router->resource('fabrics', FabricController::class);
        $router->resource('colors', ColorController::class);
        $router->resource('heel-heights', HeelHeightController::class);
        $router->resource('seasons', SeasonController::class);
        $router->resource('styles', StyleController::class);
        $router->resource('tags', TagController::class);
        $router->resource('tag_groups', TagGroupController::class);
        $router->resource('brands', BrandController::class);
        $router->resource('manufacturers', ManufacturerController::class);
        $router->resource('collections', CollectionController::class);
        $router->resource('country-of-origin', CountryOfOriginController::class);
    });

    $router->group(['prefix' => 'config', 'as' => 'config.'], function (Router $router) {
        $router->resource('payment-methods', Config\PaymentController::class);
        $router->resource('delivery-methods', Config\DeliveryController::class);
        $router->resource('currencies', Config\CurrencyController::class);
        $router->get('installment', Config\InstallmentForm::class);
        $router->get('feedback', Config\FeedbackForm::class);
        $router->get('sms', Config\SmsForm::class);
        $router->get('newsletter_for_registered', Config\NewsletterForm::class);
        $router->get('sending-tracks', Config\SendingTracksForm::class);
        $router->get('auto-order-statuses', Config\AutoOrderStatusesForm::class);
    });

    $router->group(['prefix' => 'bnrs'], function (Router $router) {
        $router->get('upsell', Forms\UpsellSliders::class);
        $router->get('short-link', Forms\ShortLink\ShortLinkController::class)->name('short-link');
    });

    $router->group(['prefix' => 'bookkeeping'], function (Router $router) {
        $router->resource('payments', Bookkeeping\PaymentController::class);
    });

    $router->group(['prefix' => 'docs'], function (Router $router) {
        $router->resource('edit', DocController::class);
        $router->get('{doc:slug}', DocController::class);
    });

    $router->group(['prefix' => 'departures'], function (Router $router) {
        $router->resource('order-to-send', Departures\OrderToSendController::class);
        $router->resource('batches', Departures\BatchController::class);
        $router->resource('track-numbers', Departures\OrderTrackController::class);
    });

    $router->group(['prefix' => 'seo'], function (Router $router) {
        $router->resource('seo-links', SeoLinkController::class);
    });
    $router->resource('cities', CityController::class);

    $router->resource('media', MediaController::class);

    $router->get('send-sms', Forms\Sms::class);

    $router->resource('stock', StockController::class);

    // legacy
    $router->any('config/rating', RatingController::class);

    // Automation
    $router->group(['prefix' => 'automation', 'as' => 'automation.'], function (Router $router) {
        $router->resource('inventory', Automation\InventoryController::class);
        $router->resource('stock', Automation\StockController::class);
        $router->get('stock-update', [Automation\StockController::class, 'updateAvailability'])->name('stock-update');
        $router->get('inventory-blacklist', Automation\InventoryBlacklistForm::class);
    });

    // Automation
    $router->group(['prefix' => 'analytics', 'as' => 'analytics.'], function (Router $router) {
        $router->resource('countries', Analytics\CountriesController::class);
        $router->resource('payment-methods', Analytics\PaymentMethodsController::class);
        $router->resource('delivery-methods', Analytics\DeliveryMethodsController::class);
        $router->resource('manager-customers', Analytics\ManagerCustomersController::class);
        $router->resource('manager-order-items', Analytics\ManagerOrderItemsController::class);
        $router->resource('type', Analytics\OrderTypeController::class);
        $router->resource('source', Analytics\OrderSourceController::class);
        $router->resource('source-detail', Analytics\OrderSourceDetailController::class);
    });

    // Orders distribution
    $router->group(['prefix' => 'orders_distribution'], function (Router $router) {
        $router->get('settings', [SettingsController::class, 'index']);
        $router->resource('statistic', StatisticController::class);
    });

    $router->group(['prefix' => 'offline', 'as' => 'offline.'], function (Router $router) {
        $router->resource('displacement', DisplacementController::class);
    });

    // logs
    $router->group(['prefix' => 'logs', 'as' => 'logs.'], function (Router $router) {
        $router->resource('sms', Logs\SmsController::class);
        $router->resource('inventory', Logs\InventoryController::class);
        $router->resource('order-item-statuses', Logs\OrderItemStatusController::class);
    });

    // debug
    $router->group(['prefix' => 'debug'], function (Router $router) {
        $router->any('clear-cache', CacheController::class);
    });
    Route::get('debug', [DebugController::class, 'index']);
    Route::get('phpinfo', [DebugController::class, 'phpinfo']);
    Route::get('debug-sentry', function (): never {
        throw new \Exception('Debug Sentry error!');
    });
});
