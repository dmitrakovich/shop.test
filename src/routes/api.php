<?php

use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\TemporaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('app-init', [AppController::class, 'init']);
Route::get('catalog/{path?}', [TemporaryController::class, 'catalog'])->where('path', '[a-zA-Z0-9/_-]+');
