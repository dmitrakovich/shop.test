<?php

use Illuminate\Routing\Router;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebugController;
use App\Admin\Controllers\SkladController;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'as'            => config('admin.route.prefix') . '.',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('users', UserController::class);
    $router->resource('categories', CategoryController::class);
    $router->resource('fabrics', FabricController::class);
    $router->resource('sizes', SizeController::class);
    $router->resource('colors', ColorController::class);
    $router->resource('heel-heights', HeelHeightController::class);
    $router->resource('seasons', SeasonController::class);
    $router->resource('tags', TagController::class);
    $router->resource('brands', BrandController::class);
    $router->resource('manufacturers', ManufacturerController::class);
    $router->resource('collections', CollectionController::class);
    $router->resource('info-pages', InfoPageController::class);
    $router->resource('orders', OrderController::class);

    $router->resource('products', ProductController::class);
    $router->get('products/{product}/restore', [\App\Admin\Controllers\ProductController::class, 'restore'])->name('products.restore');

    $router->resource('payment-methods', PaymentController::class);
    $router->resource('delivery-methods', DeliveryController::class);
    $router->resource('currencies', CurrencyController::class);
    $router->any('clear-cache', CacheController::class);

    $router->group(['prefix' => 'banners'],function ($router) {
        $router->resource('banners', BannerController::class);
        $router->resource('product-carousels', ProductCarouselController::class);
        $router->get('instagram', Forms\Instagram::class);
    });

    $router->resource('sales', SaleController::class);

    $router->resource('feedbacks', FeedbackController::class);
    $router->resource('feedbacks.feedback-answers', FeedbackAnswerController::class);

    $router->resource('media', MediaController::class);

    // legacy
    $router->any('availability', AvailiabilityController::class);
    $router->any('rating', RatingController::class);
    $router->any('sklad', [SkladController::class, 'index']);

    // debug
    Route::view('/test', 'test');
    Route::get('debug', [DebugController::class, 'index']);
    Route::get('debug-sentry', function () {
        throw new Exception('Debug Sentry error!');
    });
});
