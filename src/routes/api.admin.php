<?php

use App\Admin\Controllers\Api\ProductController;
use App\Admin\Controllers\Api\StocksController;
use App\Http\Controllers\CropperController;
use Illuminate\Support\Facades\Route;

Route::prefix('product')->group(function () {
    Route::get('product', [ProductController::class, 'getById']);
    Route::get('data', [ProductController::class, 'getProductDataById']);
});
Route::get('stocks', [StocksController::class, 'get']);

// Route::post('croppic/save', [CropperController::class, 'save']);
Route::post('croppic/crop', [CropperController::class, 'crop']);
