<?php

namespace App\Models\Xml;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GoogleXml extends AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'google';
    }

    /**
     * Prepare data for xml file
     *
     * @return object
     */
    public function getPreparedData(): object
    {
        return (object)[
            'channel' => $this->getChannelData(),
            'items' => $this->getItemsData(),
        ];
    }

    /**
     * Data for header
     *
     * @return object
     */
    protected function getChannelData(): object
    {
        return (object)[
            'title' => 'Барокко',
            'link' => $this->getHost(),
            'description' => 'Интернет магазин брендовой обуви',
        ];
    }

    /**
     * Items data
     *
     * @return array
     */
    protected function getItemsData(): array
    {
        return (new ProductService)->getForXml()
            ->map(function (Product $item) {
                return (object)[
                    'id' => $item->id,
                    'link' => $this->getHost() . $item->getUrl(),
                    'size' => $item->sizes->implode('name', '/'),
                    'availability' => $item->trashed() ? 'out of stock' : 'in stock',
                    'price' => $item->getPrice(),
                    'old_price' => $item->getOldPrice(),
                    'images' => $this->getProductImages($item->getMedia()),
                    'brand' => $this->xmlSpecialChars($item->brand->name),

                    // $this->prepareSizes($item->sizes)
                ];
            })->toArray();
    }

    /**
     * Prepare sizes string from sizes list
     *
     * @param EloquentCollection $sizes
     * @return string
     */
    protected function prepareSizes(EloquentCollection $sizes): string
    {
        $sizesList = $sizes->pluck('name');
        $sizesStr = 'Размеры: ' . ($sizesList[0] ?? 'без размера');

        $useDash = false;
        $sizesListCount = count($sizesList);
        for ($i = 1; $i < $sizesListCount; $i++) {
            if (
                ($i + 1) < $sizesListCount
                && $sizesList[$i - 1] == ((int)$sizesList[$i] - 1)
                && $sizesList[$i + 1] == ((int)$sizesList[$i] + 1)
            ) {
                $sizesStr .= $useDash ? '' : '-';
                $useDash = true;
            } else {
                $sizesStr .= ($useDash ? '' : ',') . $sizesList[$i];
                $useDash = false;
            }
        }

        return $sizesStr;
    }
}
