<?php

namespace App\Services\Feeds;

use App\Models\Product;
use App\Models\Size;

/**
 * Ads feeds skip shoe models that have only one or two sizes left.
 * One-size items (bags, accessories) stay: they have no size range.
 */
final class SizeRangeFilter
{
    public const int MIN_SHOE_SIZES = 3;

    public static function eligible(Product $product): bool
    {
        $shoeSizesCount = $product->sizes
            ->reject(fn (Size $size): bool => $size->id === Size::ONE_SIZE_ID)
            ->count();

        if ($shoeSizesCount === 0) {
            return true;
        }

        return $shoeSizesCount >= self::MIN_SHOE_SIZES;
    }
}
