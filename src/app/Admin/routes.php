<?php

use App\Admin\Controllers\SkladController;
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
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('info-pages', InfoPageController::class);
    $router->resource('orders', \OrderController::class);
    $router->get('orders/{order}/process', [\App\Admin\Controllers\OrderController::class, 'process'])->name('orders.process');
    $router->get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');

    $router->group(['prefix' => 'users'], function ($router) {
        $router->resource('users', Users\UserController::class);
        $router->resource('groups', Users\GroupController::class);
    });

    $router->resource('products', ProductControllerOld::class); // !!!
    $router->get('products/{product}/restore', [\App\Admin\Controllers\ProductController::class, 'restore'])->name('products.restore');

    $router->group(['prefix' => 'product-attributes', 'namespace' => 'ProductAttributes'], function ($router) {
        $router->resource('categories', CategoryController::class);
        $router->resource('fabrics', FabricController::class);
        $router->resource('sizes', SizeController::class);
        $router->resource('colors', ColorController::class);
        $router->resource('heel-heights', HeelHeightController::class);
        $router->resource('seasons', SeasonController::class);
        $router->resource('styles', StyleController::class);
        $router->resource('tags', TagController::class);
        $router->resource('brands', BrandController::class);
        $router->resource('manufacturers', ManufacturerController::class);
        $router->resource('collections', CollectionController::class);
    });

    $router->group(['prefix' => 'config', 'namespace' => 'Config', 'as' => 'config.'], function ($router) {
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
    });

    $router->group(['prefix' => 'bnrs'], function ($router) {
        $router->resource('bnrs', BannerController::class);
        $router->resource('index-links', IndexLinkController::class);
        $router->resource('product-carousels', ProductCarouselController::class);
        $router->get('imidj', Forms\ImidjSlider::class);
        $router->get('similar-products', Forms\SimilarProductsSlider::class);
        $router->get('recent-products', Forms\RecentProductsSlider::class);
        $router->get('product-group', Forms\ProductGroupSlider::class);
        $router->get('instagram', Forms\Instagram::class);
        $router->get('short-link', Forms\ShortLink\ShortLinkController::class)->name('short-link');
    });

    $router->group(['prefix' => 'bookkeeping'], function ($router) {
        $router->resource('payments', Bookkeeping\PaymentController::class);
    });

    $router->group(['prefix' => 'docs'], function ($router) {
        $router->resource('edit', DocController::class);
        $router->get('{doc:slug}', DocController::class);
    });

    $router->group(['prefix' => 'seo'], function ($router) {
        $router->resource('seo-links', Seo\SeoLinkController::class);
    });
    $router->resource('cities', CityController::class);

    $router->resource('sales', SaleController::class);

    $router->resource('feedbacks', FeedbackController::class);
    $router->resource('feedbacks.feedback-answers', FeedbackAnswerController::class);

    $router->resource('media', MediaController::class);

    $router->get('send-sms', Forms\Sms::class);

    $router->resource('stock', StockController::class);

    // legacy
    $router->any('availability', AvailiabilityController::class);
    $router->any('rating', RatingController::class);
    $router->any('sklad', [SkladController::class, 'index']);

    // Automation
    $router->group(['prefix' => 'automation', 'namespace' => 'Automation', 'as' => 'automation.'], function ($router) {
        $router->resource('inventory', InventoryController::class);
        $router->resource('stock', StockController::class);
    });

    // logs
    $router->group(['prefix' => 'logs', 'namespace' => 'Logs', 'as' => 'logs.'], function ($router) {
        $router->resource('sms', SmsController::class);
        $router->resource('inventory', InventoryController::class);
    });

    // debug
    $router->group(['prefix' => 'debug', 'namespace' => 'Debug'], function ($router) {
        $router->any('clear-cache', CacheController::class);
    });
    Route::view('/test', 'test');
    Route::get('debug', [DebugController::class, 'index']);
    Route::get('phpinfo', [DebugController::class, 'phpinfo']);
    Route::get('debug-sentry', function (): never {
        throw new Exception('Debug Sentry error!');
    });
});
