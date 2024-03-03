<?php

use App\Admin\Controllers\Api\ProductController;
use App\Admin\Controllers\Api\StocksController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CropperController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('users', [RegisteredUserController::class, 'sync']);
    Route::post('orders', [OrderController::class, 'sync']);
    Route::get('product/{availableSizesFull:one_c_product_id}/url', [ApiProductController::class, 'getUrl']);
});

Route::post('/payment/webhook/{code}', [PaymentController::class, 'webhook']);

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
