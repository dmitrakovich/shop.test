<?php

namespace App\Enums;

use App\Models\Ads\Banner;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

enum MorphMap: string
{
    case Banner = 'banner';
    // case Product = 'product';

    /**
     * Get the morph map array.
     *
     * @return array<string, class-string<Model>>
     */
    public static function getMorphMap(): array
    {
        return [
            self::Banner->value => Banner::class,
            // self::Product->value => Product::class,
        ];
    }
}
