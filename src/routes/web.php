<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InfoPageController;
use App\Http\Controllers\PopupController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialSubscriptionController;
use App\Http\Middleware\OnlyAjax;
use App\Http\Requests\FilterRequest;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

require __DIR__ . '/redirect.php';

Route::get('/', [IndexController::class, 'index'])->name('index-page');
Route::get('pay/erip/{payment_id?}', [PaymentController::class, 'erip'])->name('pay.erip');
Route::get('pay/yandex/{code}', [PaymentController::class, 'yandex'])->name('pay.yandex');
Route::get('pay/link-code/{code}', [PaymentController::class, 'linkCode'])->name('pay.link-code');
Route::post('pay/check-link-code/{code}', [PaymentController::class, 'checkLinkCode'])->name('pay.check-link-code');

Route::get('terms', [InfoPageController::class, 'terms'])->name('info.terms');
Route::get('policy', [InfoPageController::class, 'policy'])->name('info.policy');
Route::get('online-shopping/{slug?}', [InfoPageController::class, 'index'])->name('info');
Route::get('shops', [InfoPageController::class, 'shops'])->name('static-shops');

Route::group(['prefix' => 'channel', 'as' => 'channel.'], function () {
    Route::view('/', 'channel')->name('channel');
    Route::get('subscribe/{channel}/{eventId}', [SocialSubscriptionController::class, 'subscribe'])->name('subscribe');
});

require __DIR__ . '/auth.php';

Route::get('feedbacks/{type?}', [FeedbackController::class, 'index'])->name('feedbacks');
Route::middleware('captcha')->post('feedbacks', [FeedbackController::class, 'store'])->name('feedbacks.store');

// dashboard
Route::prefix('dashboard')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('profile', [DashboardController::class, 'edit'])->name('dashboard-profile');
        Route::patch('profile/{user}/update', [DashboardController::class, 'update'])->name('dashboard-profile-update');
    });
    Route::resource('favorites', FavoriteController::class)->only('index');
    Route::view('card', 'dashboard.card')->name('dashboard-card');
    Route::get('{orders?}', fn () => redirect()->route('orders.index'));
});
Route::resource('favorites', FavoriteController::class)->only(['store', 'destroy']);

Route::post('currency/switch', [CurrencyController::class, 'switch'])->name('currency-switcher');

Route::get('product/{product:slug}', [ProductController::class, 'show'])->withTrashed()->name('product.show');

Route::group([], function () {
    Route::post('/quick/{product}', [ProductController::class, 'quickView'])->withTrashed()->name('product.quick');
    Route::get('ajax-next-page', [CatalogController::class, 'ajaxNextPage']);
    Route::post('price-filter/{path?}', [CatalogController::class, 'priceFilter'])
        ->where('path', '[a-zA-Z0-9/_-]+');

    $check_catalog = function (FilterRequest $request) {
        $path = $request->route('path');
        $slug = (string)Str::of($path)->explode('/')->last();
        $url = Url::search($slug);

        return $url?->model_type === Product::class
            ? redirect(status: 301)->route('product.show', $url->model()->firstOrFail()->slug)
            : app(CatalogController::class)->show($request);
    };
    Route::get('catalog/city-{city}/{path?}', $check_catalog)->where('path', '[a-zA-Z0-9/_-]+')->name('shop-city');
    Route::get('catalog/{path?}', $check_catalog)->where('path', '[a-zA-Z0-9/_-]+')->name('shop');

    Route::prefix('cart')->middleware('cache.headers:no_cache;no_store;max_age=0;must_revalidate')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart');
        Route::post('add', [CartController::class, 'addToCart'])->name('cart-add');
        Route::get('delete/{item}', [CartController::class, 'delete'])->name('cart-delete');
        Route::post('apply-promocode', [CartController::class, 'applyPromoCode'])->name('cart-apply-promocode');
        Route::post('buy-one-click', [CartController::class, 'buyOneClick'])->name('cart-buy-one-click');
        Route::get('final', [CartController::class, 'final'])->name('cart-final');
    });
    Route::resource('orders', OrderController::class)->only('store');
    Route::resource('orders', OrderController::class)->only('index')->middleware('auth')->name('index', 'orders.index');
});

Route::prefix('popup')->controller(PopupController::class)->middleware(OnlyAjax::class)->group(function () {
    Route::prefix('offer')->group(function () {
        Route::get('register', 'offerToRegister');
    });
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap.{path?}.xml', [SitemapController::class, 'path'])->where('path', '.*');

Route::fallback(fn () => view('errors.404'));
