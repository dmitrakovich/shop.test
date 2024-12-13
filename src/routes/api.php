<?php

use App\Http\Controllers\Api\TemporaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->middleware(['web'])->group(function () {
    Route::get('app-init', [TemporaryController::class, 'appInit']);
    Route::get('catalog/{path?}', [TemporaryController::class, 'catalog'])->where('path', '[a-zA-Z0-9/_-]+');
});
