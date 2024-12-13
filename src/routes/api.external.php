<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Shop\PaymentController;
use Illuminate\Support\Facades\Route;

// todo: remove routes with this prefix after move to external
Route::prefix('api')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('product/{availableSizesFull:one_c_product_id}/url', [AdminProductController::class, 'getUrl']);
    });

    Route::post('/payment/webhook/{code}', [PaymentController::class, 'webhook']);
});

Route::prefix('api/external')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('product/{availableSizesFull:one_c_product_id}/url', [AdminProductController::class, 'getUrl']);
    });

    Route::post('/payment/webhook/{code}', [PaymentController::class, 'webhook']);
});
