<?php

use App\Http\Controllers\Api\V2\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog API v2 (test / next frontend)
|--------------------------------------------------------------------------
|
| Elasticsearch-first catalog. Response contract may diverge from v1.
|
*/

Route::get('catalog/{path?}', [CatalogController::class, 'index'])
    ->where('path', '[a-zA-Z0-9/_-]+')
    ->name('catalog.index');
