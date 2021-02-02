<?php

use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('/users', UserController::class);
    $router->resource('categories', CategoryController::class);
    $router->resource('fabrics', FabricController::class);
    $router->resource('sizes', SizeController::class);
    $router->resource('colors', ColorController::class);
    $router->resource('heel-heights', HeelHeightController::class);
    $router->resource('seasons', SeasonController::class);
    $router->resource('tags', TagController::class);
    $router->resource('brands', BrandController::class);
    $router->resource('products', ProductController::class);

    // legacy
    $router->any('sklad', function () { return require_once app_path('Admin/legacy/sklad.php'); });
    $router->any('availability', function () { return require_once app_path('Admin/legacy/availability.php'); });
    $router->any('rating', function () { return require_once app_path('Admin/legacy/rating.php'); });
});
