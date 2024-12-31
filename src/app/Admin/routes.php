<?php

use App\Admin\Controllers\OrderController as AdminOrderController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\Shop\OrderController;
use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'as' => config('admin.route.prefix') . '.',
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->resource('info-pages', InfoPageController::class);

    $router->group(['prefix' => 'orders', 'namespace' => 'Orders', 'as' => 'orders.'], function (Router $router) {
        $router->resource('offline', OfflineOrderController::class);
    });
    // todo: move to orders
    $router->resource('orders', \OrderController::class);
    $router->resource('order-items', OrderItemController::class);
    $router->resource('order-comments', OrderCommentController::class);
    $router->get('orders/{order}/process', [\App\Admin\Controllers\OrderController::class, 'process'])->name('orders.process');
    $router->get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    $router->post('orders/add-user-by-phone', [AdminOrderController::class, 'addUserByPhone']);
    $router->post('orders/change-user-by-phone', [AdminOrderController::class, 'changeUserByPhone']);
    $router->post('orders/update-user-address', [AdminOrderController::class, 'updateUserAddress']);
    $router->post('orders/add-order-comment', [AdminOrderController::class, 'addOrderComment']);

    $router->group(['prefix' => 'users'], function (Router $router) {
        $router->resource('users', Users\UserController::class);
        $router->resource('groups', Users\GroupController::class);
    });

    $router->resource('products', ProductController::class);
    $router->get('products/{product}/restore', [\App\Admin\Controllers\ProductController::class, 'restore'])->name('products.restore');

    $router->group(['prefix' => 'product-attributes', 'namespace' => 'ProductAttributes'], function (Router $router) {
        $router->resource('categories', CategoryController::class);
        $router->resource('fabrics', FabricController::class);
        $router->resource('sizes', SizeController::class);
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

    $router->group(['prefix' => 'config', 'namespace' => 'Config', 'as' => 'config.'], function (Router $router) {
        $router->resource('payment-methods', PaymentController::class);
        $router->resource('delivery-methods', DeliveryController::class);
        $router->resource('currencies', CurrencyController::class);
        $router->resource('order-statuses', OrderStatusController::class);
        $router->resource('order-item-statuses', OrderItemStatusController::class);
        $router->get('installment', InstallmentForm::class);
        $router->get('feedback', FeedbackForm::class);
        $router->get('sms', SmsForm::class);
        $router->get('instagram-token', InstagramTokenForm::class);
        $router->get('newsletter_for_registered', NewsletterForm::class);
        $router->get('sending-tracks', SendingTracksForm::class);
        $router->get('auto-order-statuses', AutoOrderStatusesForm::class);
    });

    $router->group(['prefix' => 'bnrs'], function (Router $router) {
        $router->resource('bnrs', BannerController::class);
        $router->resource('index-links', IndexLinkController::class);
        $router->resource('product-carousels', ProductCarouselController::class);
        $router->get('imidj', Forms\ImidjSlider::class);
        $router->get('similar-products', Forms\SimilarProductsSlider::class);
        $router->get('upsell', Forms\UpsellSliders::class);
        $router->get('recent-products', Forms\RecentProductsSlider::class);
        $router->get('product-group', Forms\ProductGroupSlider::class);
        $router->get('instagram', Forms\Instagram::class);
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
        $router->resource('seo-links', Seo\SeoLinkController::class);
    });
    $router->resource('cities', CityController::class);

    $router->resource('feedbacks', FeedbackController::class);
    $router->resource('feedbacks.feedback-answers', FeedbackAnswerController::class);

    $router->resource('media', MediaController::class);

    $router->get('send-sms', Forms\Sms::class);

    $router->resource('stock', StockController::class);

    // legacy
    $router->any('config/rating', RatingController::class);

    // Automation
    $router->group(['prefix' => 'automation', 'namespace' => 'Automation', 'as' => 'automation.'], function (Router $router) {
        $router->resource('inventory', InventoryController::class);
        $router->resource('stock', StockController::class);
        $router->get('stock-update', [\App\Admin\Controllers\Automation\StockController::class, 'updateAvailability'])->name('stock-update');
        $router->get('inventory-blacklist', InventoryBlacklistForm::class);
    });

    // Automation
    $router->group(['prefix' => 'analytics', 'namespace' => 'Analytics', 'as' => 'analytics.'], function (Router $router) {
        $router->resource('countries', CountriesController::class);
        $router->resource('payment-methods', PaymentMethodsController::class);
        $router->resource('delivery-methods', DeliveryMethodsController::class);
        $router->resource('manager-customers', ManagerCustomersController::class);
        $router->resource('manager-order-items', ManagerOrderItemsController::class);
        $router->resource('type', OrderTypeController::class);
        $router->resource('source', OrderSourceController::class);
        $router->resource('source-detail', OrderSourceDetailController::class);
    });

    // Orders distribution
    $router->group(['prefix' => 'orders_distribution'], function (Router $router) {
        $router->get('settings', [\App\Admin\Controllers\OrdersDistribution\SettingsController::class, 'index']);
        $router->resource('statistic', \App\Admin\Controllers\OrdersDistribution\StatisticController::class);
    });

    $router->group(['prefix' => 'offline', 'namespace' => 'Offline', 'as' => 'offline.'], function (Router $router) {
        $router->resource('displacement', DisplacementController::class);
    });

    // logs
    $router->group(['prefix' => 'logs', 'namespace' => 'Logs', 'as' => 'logs.'], function (Router $router) {
        $router->resource('sms', SmsController::class);
        $router->resource('inventory', InventoryController::class);
        $router->resource('order-item-statuses', OrderItemStatusController::class);
    });

    // debug
    $router->group(['prefix' => 'debug', 'namespace' => 'Debug'], function (Router $router) {
        $router->any('clear-cache', CacheController::class);
    });
    Route::view('/test', 'test');
    Route::get('debug', [DebugController::class, 'index']);
    Route::get('phpinfo', [DebugController::class, 'phpinfo']);
    Route::get('debug-sentry', function (): never {
        throw new Exception('Debug Sentry error!');
    });
});
