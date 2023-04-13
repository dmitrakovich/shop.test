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
        $sizeFieldsSum = implode(' + ', self::getSizeFields());

        return self::query()->whereRaw("$sizeFieldsSum = 0")->delete();
    }

    /**
     * Returns an array of size fields.
     * The array includes the "size_none" field and all size fields from "size_31" to "size_48".
     */
    public static function getSizeFields(): array
    {
        $sizeFields = ['size_none'];
        for ($i = 31; $i <= 48; $i++) {
            $sizeFields[] = 'size_' . $i;
        }

        return $sizeFields;
    }

    /**
     * Returns an array of size fields wrapped in the SUM function.
     *
     * The array includes the "size_none" field and all size fields, with each field name
     * preceded by the SUM function and followed by the original field name as an alias.
     */
    public static function getSumWrappedSizeFields(): array
    {
        return array_map(fn (string $size) => "SUM($size) as $size", self::getSizeFields());
    }

    /**
     * Converts a given field name to its corresponding Size ID.
     */
    public static function convertFieldToSizeId(string $field): int
    {
        return match ($field) {
            'size_none' => 1,
            'size_31' => 20,
            'size_32' => 21,
            'size_33' => 2,
            'size_34' => 3,
            'size_35' => 4,
            'size_36' => 5,
            'size_37' => 6,
            'size_38' => 7,
            'size_39' => 8,
            'size_40' => 9,
            'size_41' => 10,
            'size_42' => 22,
            'size_43' => 23,
            'size_44' => 24,
            'size_45' => 25,
            'size_46' => 26,
            'size_47' => 27,
            'size_48' => 28,
        };
    }
}
