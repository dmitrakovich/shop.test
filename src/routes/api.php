<?php

use App\Admin\Controllers\Api\ProductController;
use App\Admin\Controllers\Api\StocksController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\TemporaryController;
use App\Http\Controllers\CropperController;
use App\Http\Controllers\Shop\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('product/{availableSizesFull:one_c_product_id}/url', [AdminProductController::class, 'getUrl']); // !! for 1C
});

Route::post('/payment/webhook/{code}', [PaymentController::class, 'webhook']);

// admin api
Route::group([
    'excluded_middleware' => 'throttle:api',
], function () {
    Route::prefix('product')->group(function () {
        Route::get('product', [ProductController::class, 'getById']);
        Route::get('data', [ProductController::class, 'getProductDataById']);
    });
    Route::get('stocks', [StocksController::class, 'get']);
});

// Route::post('croppic/save', [CropperController::class, 'save']);
Route::post('croppic/crop', [CropperController::class, 'crop']);

Route::prefix('v1')->middleware(['web'])->group(function () {
    Route::get('app-init', [TemporaryController::class, 'appInit']);
    Route::get('catalog/{path?}', [TemporaryController::class, 'catalog'])->where('path', '[a-zA-Z0-9/_-]+');
});
