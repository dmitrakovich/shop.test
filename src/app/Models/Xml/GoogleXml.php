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
     * @return array
     */
    public function getPreparedData(): array
    {
        return [
            'channel' => $this->getChannelData(),
            'items' => $this->getItemsData(),
        ];
    }

    /**
     * Data for header
     *
     * @return array
     */
    protected function getChannelData(): array
    {
        return [
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
        return Product::with(['category'])->get()->toArray();
    }
}
