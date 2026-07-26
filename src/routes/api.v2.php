<?php

use App\Http\Controllers\Api\V2\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('catalog/{path?}', [CatalogController::class, 'index'])
    ->where('path', '[a-zA-Z0-9/_-]+');
