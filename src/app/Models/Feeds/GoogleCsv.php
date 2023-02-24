<?php

namespace App\Models\Feeds;

use App\Facades\Currency;
use App\Models\Product;
use App\Services\ProductService;

/**
 * Class GoogleCsv
 *
 * @see https://support.google.com/google-ads/answer/6053288#zippy=%2Ccustom
 */
class GoogleCsv extends AbstractFeed
{
    /**
     * @var string
     */
    const FILE_TYPE = 'csv';

    /**
     * @var int
     */
    const MAX_IMAGE_COUNT = 1;

    /**
     * @var \App\Models\Currency
     */
    protected $currency;

    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'google';
    }

    /**
     * Prepare data for csv file
     */
    public function getPreparedData(): object
    {
        return (object)[
            'headers' => $this->getHeaders(),
            'rows' => $this->getRows(),
        ];
    }

    /**
     * Columns headers
     */
    protected function getHeaders(): array
    {
        return [
            'ID',
            'Item title',
            'Final URL',
            'Image URL',
            'Item description',
            'Item category',
            'Price',
            'Sale price',
        ];
    }

    /**
     * Rows data
     */
    protected function getRows(): array
    {
        $this->currency = Currency::getCurrentCurrency();

        return (new ProductService)->getForFeed()
            ->map(function (Product $item) {
                return [
                    $item->id,
                    $this->getItemTitle($item),
                    $this->getHost() . $item->getUrl(),
                    $this->getProductImages($item->getMedia())[0],
                    $this->getDescription($item),
                    $item->category->name,
                    $this->formatPrice($item->getOldPrice()),
                    $this->getSalePrice($item->getPrice(), $item->getOldPrice()),
                ];
            })->toArray();
    }

    /**
     * Get sale price if exist
     */
    protected function getSalePrice(float $price, float $oldPrice): ?string
    {
        return $price < $oldPrice ? $this->formatPrice($price) : null;
    }

    /**
     * Format price for google csv
     */
    protected function formatPrice(float $price): string
    {
        return number_format($price, 2) . ' ' . $this->currency->code;
    }

    /**
     * Prepared item title
     */
    protected function getItemTitle(Product $item): string
    {
        return $item->category->name . ' ' . $item->sku;
    }

    /**
     * Generate product description
     */
    public function getDescription(Product $product): string
    {
        return "Цвет: {$product->color_txt}. {$this->sizesToString($product->sizes)}";
    }
}
