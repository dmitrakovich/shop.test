<?php

use App\Admin\Controllers\Automation;
use App\Admin\Controllers\Bookkeeping;
use App\Admin\Controllers\Config;
use App\Admin\Controllers\Departures;
use App\Admin\Controllers\Logs;
use App\Admin\Controllers\Offline\DisplacementController;
use App\Admin\Controllers\OrderCommentController;
use App\Admin\Controllers\OrderController as AdminOrderController;
use App\Admin\Controllers\OrderItemController;
use App\Admin\Controllers\Orders\OfflineOrderController;
use App\Http\Controllers\Shop\OrderController;
use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix' => 'old-admin',
    'as' => 'admin.',
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->group(['prefix' => 'orders', 'as' => 'orders.'], function (Router $router) {
        $router->resource('offline', OfflineOrderController::class);
    });
    // todo: move to orders
    $router->resource('orders', AdminOrderController::class);
    $router->resource('order-items', OrderItemController::class);
    // $router->resource('order-comments', OrderCommentController::class); // todo: maybe excess
    $router->get('orders/{order}/process', [AdminOrderController::class, 'process'])->name('orders.process');
    $router->get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    $router->post('orders/add-user-by-phone', [AdminOrderController::class, 'addUserByPhone']);
    $router->post('orders/change-user-by-phone', [AdminOrderController::class, 'changeUserByPhone']);
    $router->post('orders/update-user-address', [AdminOrderController::class, 'updateUserAddress']);
    $router->post('orders/add-order-comment', [AdminOrderController::class, 'addOrderComment']);

    $router->group(['prefix' => 'config', 'as' => 'config.'], function (Router $router) {
        $router->get('newsletter_for_registered', Config\NewsletterForm::class);
        $router->get('sending-tracks', Config\SendingTracksForm::class);
    });

    $router->group(['prefix' => 'bookkeeping'], function (Router $router) {
        $router->resource('payments', Bookkeeping\PaymentController::class);
    });

    $router->group(['prefix' => 'departures'], function (Router $router) {
        $router->resource('order-to-send', Departures\OrderToSendController::class);
        $router->resource('batches', Departures\BatchController::class);
        $router->resource('track-numbers', Departures\OrderTrackController::class);
    });

    // Automation
    $router->group(['prefix' => 'automation', 'as' => 'automation.'], function (Router $router) {
        $router->resource('inventory', Automation\InventoryController::class);
        $router->resource('stock', Automation\StockController::class);
        $router->get('stock-update', [Automation\StockController::class, 'updateAvailability'])->name('stock-update');
        $router->get('inventory-blacklist', Automation\InventoryBlacklistForm::class);
    });

    $router->group(['prefix' => 'offline', 'as' => 'offline.'], function (Router $router) {
        $router->resource('displacement', DisplacementController::class);
    });

    // logs
    $router->group(['prefix' => 'logs', 'as' => 'logs.'], function (Router $router) {
        $router->resource('inventory', Logs\InventoryController::class);
        $router->resource('order-item-statuses', Logs\OrderItemStatusController::class);
    });
});
