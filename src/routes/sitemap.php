<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => SitemapController::class, 'as' => 'sitemap.'], function () {
    Route::get('/sitemap.xml', 'index')->name('index');
    Route::get('/sitemap.static.xml', 'static')->name('static');
    Route::get('/sitemap.products.xml', 'products')->name('products');
    Route::get('/sitemap.catalog.categories.xml', 'categories')->name('catalog.categories');
    Route::get('/sitemap.catalog.brands.xml', 'brands')->name('catalog.brands');
    Route::get('/sitemap.catalog.categories_and_{another}_and_{another2}.xml', 'catalog3')->name('catalog.catalog3');
    Route::get('/sitemap.catalog.categories_and_{another}.xml', 'catalog2')->name('catalog.catalog2');
});
