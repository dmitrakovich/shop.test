<?php

use App\Facades\Device;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\InfoPageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
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
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->prefix('account')->as('account.')->group(function () {
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [UserController::class, 'show'])->name('show');
        Route::put('/', [UserController::class, 'update'])->name('update');
    });
});

Route::get('app-init', [AppController::class, 'init']);
Route::get('catalog/{path?}', [CatalogController::class, 'index'])->where('path', '[a-zA-Z0-9/_-]+');
Route::get('product/{product:slug}', [CatalogController::class, 'show'])->withTrashed()->name('product.show');

Route::get('main-page', [InfoPageController::class, 'main']);
Route::get('info-page/{page:slug}', [InfoPageController::class, 'show']);
Route::get('shops', [InfoPageController::class, 'shops']);

Route::prefix('cart')->as('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('add', [CartController::class, 'addToCart'])->name('add');
    Route::delete('remove/{itemId}', [CartController::class, 'removeItem'])->name('remove');
    Route::post('clear', [CartController::class, 'clear'])->name('clear');

    Route::get('deliveries', [CartController::class, 'getDeliveries'])->name('deliveries');
    Route::get('payments', [CartController::class, 'getPayments'])->name('payments');
});

Route::prefix('favorites')->as('favorites.')->group(function () {
    Route::get('/', [FavoriteController::class, 'index'])->name('index');
    Route::post('toggle/{product}', [FavoriteController::class, 'toggle'])->name('toggle');
});

Route::prefix('orders')->as('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->middleware('auth:sanctum')->name('index');
    Route::post('checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('oneclick', [OrderController::class, 'oneclick'])->name('oneclick');
});

Route::prefix('feedbacks')->as('feedbacks.')->group(function () {
    Route::get('/', [FeedbackController::class, 'index'])->name('index');
    Route::middleware('captcha')->group(function () {
        Route::post('/', [FeedbackController::class, 'store'])->name('store');
        Route::post('{feedback}/answers', [FeedbackController::class, 'storeAnswer'])->name('answers.store');
    });
});

Route::prefix('address')->as('address.')->group(function () {
    Route::get('countries', [AddressController::class, 'getCountries']);
});
