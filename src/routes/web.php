<?php

use App\Models\Url;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InfoPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/redirect.php';

Route::get('/', [IndexController::class, 'index'])->name('index-page');
Route::get('pay/erip/{payment_id?}', [PaymentController::class, 'erip'])->name('pay.erip');

Route::get('terms', [InfoPageController::class, 'terms'])->name('info.terms');
Route::get('policy', [InfoPageController::class, 'policy'])->name('info.policy');
Route::get('online-shopping/{slug?}', [InfoPageController::class, 'index'])->name('info');

Route::view('shops', 'static.shops')->name('static-shops');

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

Route::group(['namespace' => 'Shop'], function () {
    Route::post('/quick/{product}', [ProductController::class, 'quickView'])->name('product.quick');
    Route::get('ajax-next-page', [CatalogController::class, 'ajaxNextPage']);

    Route::get('catalog/{path?}', function () {
        $request = Route::getCurrentRequest();
        $slug = Str::of($request->path())->explode('/')->last();
        $url = Url::search($slug);

        if (isset($url) && $url['model_type'] === Product::class) {
            return app(ProductController::class)->show($url->model_id);
        } else {
            return app(CatalogController::class)->show(FilterRequest::createFrom($request));
        }
    })
        ->where('path', '[a-zA-Z0-9/_-]+')
        ->name('shop');

    Route::prefix('cart')->group(function () { // routes for cart pages
        Route::get('/', [CartController::class, 'index'])->name('cart');
        Route::post('add', [CartController::class, 'addToCart'])->name('cart-add');
        Route::get('delete/{item}', [CartController::class, 'delete'])->name('cart-delete');
        Route::post('buy-one-click', [CartController::class, 'buyOneClick'])->name('cart-buy-one-click');
        Route::get('final', [CartController::class, 'final'])->name('cart-final');
    });
    Route::resource('orders', OrderController::class)->only('store');
    Route::resource('orders', OrderController::class)->only('index')->middleware('auth');
});

require __DIR__ . '/sitemap.php';

// Route::fallback(fn () => 'Хм… Почему ты оказался здесь?');
