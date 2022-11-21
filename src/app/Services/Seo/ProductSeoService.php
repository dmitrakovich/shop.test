<?php

namespace App\Services\Seo;

use App\Models\Product;
use SeoFacade;

class ProductSeoService
{
    private Product $product;

    /**
     * Set current product
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Generate title for product
     */
    public function getProductTitle(): string
    {
        $product = $this->product;
        $discount = $product->getSalePercentage();

        return $product->extendedName() . ' ' . ($discount ? "со скидкой {$discount}%." : '- новинка!');
    }

    /**
     * Generate description for product
     */
    public function getProductDescription(): string
    {
        $product = $this->product;
        $description = $this->getProductTitle($product);

        if (!empty($product->color_txt)) {
            $description .= " Цвет: {$product->color_txt}.";
        }
        if ($product->sizes->isNotEmpty() && !$product->hasOneSize()) {
            $description .= ' Размеры: ' . $product->sizes->implode('name', ', ');
        }

        return $description;
    }

    /**
     * Generate product seo
     */
    public function generate(): void
    {
        SeoFacade::setTitle($this->getProductTitle())
            ->setDescription($this->getProductDescription())
            ->setImage($this->product->getFirstMedia()->getUrl('catalog'));
    }
}
