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
        $discount = $product->getSalePercentage();

        return $product->extendedName() . ' ' . ($discount ? "со скидкой {$discount}%." : '- новинка!');
    }

    /**
     * Generate description for product
     */
    public function getProductDescription(Product $product): string
    {
        $description = $this->getProductTitle($product);

        if (!empty($product->color_txt)) {
            $description .= " Цвет: {$product->color_txt}.";
        }
        if ($product->sizes->isNotEmpty() && !$product->hasOneSize()) {
            $description .= ' Размеры: ' . $product->sizes->implode('name', ', ');
        }

        return $description;
    }
}
