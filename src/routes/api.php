<?php

use App\Facades\Device;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\CatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return [
        'user' => $request->user(),
        'device' => Device::current(),
    ];
})->middleware('auth:sanctum');

Route::get('app-init', [AppController::class, 'init']);
Route::get('catalog/{path?}', [CatalogController::class, 'index'])->where('path', '[a-zA-Z0-9/_-]+');
Route::get('product/{product:slug}', [CatalogController::class, 'show'])->withTrashed()->name('product.show');
