<?php

use App\Facades\Device;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\InfoPageController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return [
        'user' => $request->user(),
        'device' => Device::current(),
    ];
})->middleware('auth:sanctum');

Route::prefix('auth')->as('auth.')->middleware('captcha')->group(function () {
    Route::prefix('otp')->as('otp.')->group(function () {
        Route::post('send', [AuthController::class, 'sendOtp'])->name('send');
    });
    Route::post('attempt', [AuthController::class, 'attempt'])->name('attempt');
});

Route::get('app-init', [AppController::class, 'init']);
Route::get('catalog/{path?}', [CatalogController::class, 'index'])->where('path', '[a-zA-Z0-9/_-]+');
Route::get('product/{product:slug}', [CatalogController::class, 'show'])->withTrashed()->name('product.show');

Route::get('main-page', [InfoPageController::class, 'main']);
Route::get('info-page/{page:slug}', [InfoPageController::class, 'show']);

Route::prefix('cart')->as('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('add', [CartController::class, 'addToCart'])->name('add');
    Route::delete('remove/{itemId}', [CartController::class, 'removeItem'])->name('remove');
    Route::post('clear', [CartController::class, 'clear'])->name('clear');
});

Route::prefix('orders')->as('orders.')->group(function () {
    Route::post('checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('oneclick', [OrderController::class, 'oneclick'])->name('oneclick');
});
