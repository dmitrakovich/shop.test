<?php

use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog API v2
|--------------------------------------------------------------------------
|
| Elasticsearch storefront catalog. Response contract is independent from
| the rest of the storefront API under /api/v1.
|
*/

Route::get('catalog/{path?}', [CatalogController::class, 'index'])
    ->where('path', '[a-zA-Z0-9/_-]+')
    ->name('catalog.index');
