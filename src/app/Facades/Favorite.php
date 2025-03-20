<?php

namespace App\Facades;

use App\Models\Favorite as FavoriteModel;
use App\Models\Product;
use App\Services\FavoriteService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection<int,Product> getProducts()
 * @method static FavoriteModel addProduct(Product $product)
 * @method static void removeProduct(Product $product)
 * @method static void toggleProduct(Product $product)
 *
 * @see FavoriteService
 */
class Favorite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FavoriteService::class;
    }
}
