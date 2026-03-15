<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialSubscriptionController;
use App\Http\Requests\FilterRequest;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

require __DIR__ . '/redirect.php';

Route::get('pay/erip/{payment_id?}', [PaymentController::class, 'erip'])->name('pay.erip');
Route::get('pay/yandex/{code}', [PaymentController::class, 'yandex'])->name('pay.yandex');
Route::get('pay/link-code/{code}', [PaymentController::class, 'linkCode'])->name('pay.link-code');
Route::post('pay/check-link-code/{code}', [PaymentController::class, 'checkLinkCode'])->name('pay.check-link-code');

Route::group(['prefix' => 'channel', 'as' => 'channel.'], function () {
    Route::view('/', 'channel')->name('channel');
    Route::get('subscribe/{channel}/{eventId}', [SocialSubscriptionController::class, 'subscribe'])->name('subscribe');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap.{path?}.xml', [SitemapController::class, 'path'])->where('path', '.*');

//

//

// Route::get('/', [IndexController::class, 'index'])->name('index-page');
// Route::get('product/{product:slug}', [ProductController::class, 'show'])->withTrashed()->name('product.show');
// Route::get('ajax-next-page', [CatalogController::class, 'ajaxNextPage']);
// Route::post('price-filter/{path?}', [CatalogController::class, 'priceFilter'])
//     ->where('path', '[a-zA-Z0-9/_-]+');

// $check_catalog = function (FilterRequest $request) {
//     $path = $request->route('path');
//     $slug = (string)Str::of($path)->explode('/')->last();
//     $url = Url::search($slug);

//     return $url?->model_type === Product::class
//         ? redirect(status: 301)->route('product.show', $url->model()->firstOrFail()->slug)
//         : app(CatalogController::class)->show($request);
// };
// Route::get('catalog/{path?}', $check_catalog)->where('path', '[a-zA-Z0-9/_-]+')->name('shop');

//

Route::fallback(fn (Request $request) => front_redirect($request->getRequestUri()));
