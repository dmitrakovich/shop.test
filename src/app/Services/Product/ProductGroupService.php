<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Support\Facades\Cache;

class ProductGroupService
{
    /**
     * Удалить товар из группы товаров / Удалить группу если в ней нет товаров
     *
     * @param  int  $productId  - id товара
     * @param  int  $productGroupId  - id группы товаровы
     */
    public function removeFromProductGroup(int $productId, int $productGroupId): bool
    {
        if (Product::where('product_group_id', $productGroupId)->count() <= 2) {
            Product::where('product_group_id', $productGroupId)->update(['product_group_id' => null]);
            ProductGroup::where('id', $productGroupId)->delete();
        } else {
            Product::where('id', $productId)->update(['product_group_id' => null]);
        }
        $cacheConfig = config('cache_config.product_group');
        Cache::forget($cacheConfig['key'] . $productGroupId);

        return true;
    }

    /**
     * Создать группу из товаров или добавить к существующей группе
     *
     * @param  int  $curProductId  - какой id товара добавить в группу
     * @param  int  $curProductId  - к какому id товара добавить в группу
     */
    public function addToProductGroup(int $curProductId, int $toProductId): bool
    {
        $product = Product::find($toProductId);
        if ($product->product_group_id) {
            $productGroupId = $product->product_group_id;
            Product::where('id', $curProductId)->update(['product_group_id' => $product->product_group_id]);
        } else {
            $productGroup = ProductGroup::create();
            $productGroupId = $productGroup->id;
            Product::whereIn('id', [$curProductId, $toProductId])->update(['product_group_id' => $productGroup->id]);
        }
        $cacheConfig = config('cache_config.product_group');
        Cache::forget($cacheConfig['key'] . $productGroupId);

        return true;
    }
}
