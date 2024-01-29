<?php

namespace App\Admin\Models;

use App\Models\AvailableSizes;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $one_c_product_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property int|null $stock_id
 * @property string $sku
 * @property string|null $category_name
 * @property float $buy_price
 * @property float $sell_price
 * @property int $size_none
 * @property int $size_31
 * @property int $size_32
 * @property int $size_33
 * @property int $size_34
 * @property int $size_35
 * @property int $size_36
 * @property int $size_37
 * @property int $size_38
 * @property int $size_39
 * @property int $size_40
 * @property int $size_41
 * @property int $size_42
 * @property int $size_43
 * @property int $size_44
 * @property int $size_45
 * @property int $size_46
 * @property int $size_47
 * @property int $size_48
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Stock|null $stock
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Size[] $sizes
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AvailableSizesFull extends AvailableSizes
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'available_sizes_full';
}
