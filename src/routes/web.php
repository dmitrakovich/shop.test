<?php

use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialSubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/redirect.php';

Route::get('pay/erip/{payment_id?}', [PaymentController::class, 'erip'])->name('pay.erip');
Route::get('pay/yandex/{code}', [PaymentController::class, 'yandex'])->name('pay.yandex');
Route::get('pay/link-code/{code}', [PaymentController::class, 'linkCode'])->name('pay.link-code');
Route::post('pay/check-link-code/{code}', [PaymentController::class, 'checkLinkCode'])->name('pay.check-link-code');

Route::group(['prefix' => 'channel', 'as' => 'channel.'], function () {
    Route::view('/', 'channel')->name('channel');
    Route::get('subscribe/{channel}/{eventId}', [SocialSubscriptionController::class, 'subscribe'])->name('subscribe');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap.{path?}.xml', [SitemapController::class, 'path'])->where('path', '.*');

Route::fallback(fn (Request $request) => front_redirect($request->getRequestUri()));
