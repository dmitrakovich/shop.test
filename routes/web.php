<?php

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

Auth::routes();

// dashboard
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard-orders');
    });
    Route::view('orders', 'dashboard.orders')->name('dashboard-orders');
    Route::view('saved', 'dashboard.saved')->name('dashboard-saved');
    Route::view('profile', 'dashboard.profile')->name('dashboard-profile');
    Route::view('card', 'dashboard.card')->name('dashboard-card');
});