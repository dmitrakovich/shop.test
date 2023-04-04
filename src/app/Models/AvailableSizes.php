<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AvailableSizes model
 *
 * @property int $id
 * @property int $product_id
 * @property int $one_c_product_id
 * @property int $brand_id
 * @property int $category_id
 * @property int $stock_id
 * @property string $sku
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
 */
class AvailableSizes extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Remove records where sum all sizes = 0
     */
    public static function removeEmptySizes(): int
    {
        $sizeFields = ['size_none'];
        for ($i = 31; $i <= 48; $i++) {
            $sizeFields[] = 'size_' . $i;
        }
        $sizeFieldsSum = implode(' + ', $sizeFields);

        return self::query()->whereRaw("$sizeFieldsSum = 0")->delete();
    }
}
