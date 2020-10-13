<?php

use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\ProductController;
use App\Models\Url;
use Illuminate\Support\Facades\Route;

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

Route::get('/debug', function () {
    // dd(Auth::check());
    dd(Auth::id());
    dd(Auth::user());
})->middleware('auth.basic');


// Route::domain(env('APP_NAME') . '.{domain}')->group(function () {
//     Route::get('/domain', function ($domain) {
//         dd($domain);
//     });
// });


// дописать права только для админа
// вообще в админку перенести !!!
Route::prefix('clear-cache')->group(function () {
    Route::get('/app', function () {
        Artisan::call('cache:clear');
        return 'App cache is cleared';
    });
    // ... 
    Route::get('/all', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Cache::flush();
        return 'All cache is cleared';
    });
});


Route::view('/test', 'test');
Route::view('/', 'index')->name('index-page');

// static pages
Route::prefix('online-shopping')->group(function () {
    Route::view('/', 'static.instruction');
    Route::view('instruction', 'static.instruction')->name('static-instruction');
    Route::view('payment', 'static.payment')->name('static-payment');
    Route::view('delivery', 'static.delivery')->name('static-delivery');
    Route::view('return', 'static.return')->name('static-return');
    Route::view('installments', 'static.installments')->name('static-installments');
});
Route::view('shops', 'static.shops')->name('static-shops');

Auth::routes();

Route::get('reviews', 'ReviewsController@getAll')->name('reviews');

// dashboard
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard-orders');
    });
    Route::get('orders', 'DashboardController@getOrders')->name('dashboard-orders');
    Route::view('saved', 'dashboard.saved')->name('dashboard-saved');
    Route::get('profile', 'DashboardController@getProfileData')->name('dashboard-profile');
    Route::patch('profile/{user}/update', 'DashboardController@updateProfileData')->name('dashboard-profile-update');
    Route::view('card', 'dashboard.card')->name('dashboard-card');
});

Route::group(['namespace' => 'Shop'], function () {
    Route::get('catalog/{path?}', function () {        
        $request = Route::getCurrentRequest();       
        $slug = Str::of($request->path())->explode('/')->last();
        $url = Url::search($slug);

        if (isset($url) && (new $url['model_type']) instanceof App\Models\Product) {
            return (new ProductController())->show($url, $request->input());
        }
        return (new CatalogController())->show($request);
    })
        ->where('path', '[a-zA-Z0-9/_-]+')
        ->name('shop');

    Route::get('cart', 'CartController@index')->name('cart');
    Route::post('add-to-cart', 'CartController@addToCart')->name('addToCart');
    Route::post('cart-submit', 'CartController@submit')->name('cartSubmit');
});

Route::fallback(function() {
    return 'Хм… Почему ты оказался здесь?';
});
