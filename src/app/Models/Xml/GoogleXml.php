<?php

namespace App\Models\Xml;

use App\Models\Product;

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
        return Product::with([
                'category',
                'media',
            ])
            ->withTrashed()
            ->limit(5) // !!!
            ->get()
            ->map(function ($item) {
                return (object)[
                    'id' => $item->id,
                    'link' => $this->getHost() . $item->getUrl(),

                    'availability' => $item->trashed() ? 'out of stock' : 'in stock',

                    'images' => $this->getProductImages($item->getMedia()),
                ];
            })->toArray();
    }
}
