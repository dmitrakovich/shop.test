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

// Route::get('/', function () {
//     return view('index');
// });


// Route::domain(env('APP_NAME') . '.{domain}')->group(function () {
//     Route::get('/domain', function ($domain) {
//         dd($domain);
//     });
// });

Route::view('/test', 'test');
Route::view('/', 'index')->name('index-page');

// static pages
Route::prefix('online-shopping')->group(function () {
    Route::view('/', 'static.kak-zakazat');
    Route::view('kak-zakazat', 'static.kak-zakazat')->name('static-kak-zakazat');
    Route::view('payment', 'static.payment')->name('static-payment');
    Route::view('delivery', 'static.delivery')->name('static-delivery');
    Route::view('return', 'static.return')->name('static-return');
    Route::view('installments', 'static.installments')->name('static-installments');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
