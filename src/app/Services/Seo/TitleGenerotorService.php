<?php

namespace App\Services\Seo;

use App\Models\Product;

class TitleGenerotorService
{
    /**
     * Generate title for product
     */
    public function getProductTitle(Product $product): string
    {
        $title = $product->extendedName() . ' ';
        $title .= ($discount = $product->getSalePercentage()) ? "со скидкой {$discount}%." : '- новинка!';

        if (!empty($product->color_txt)) {
            $title .= " Цвет: {$product->color_txt}.";
        }
        if ($product->sizes->isNotEmpty() && !$product->hasOneSize()) {
            $title .= ' Размеры: ' . $product->sizes->implode('name', ', ');
        }

        return $title;
    }
}
