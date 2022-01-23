<?php

namespace App\Models\Xml;

use App\Models\Category;
use Kalnoy\Nestedset\Collection as NestedsetCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

abstract class AbstractXml
{
    /**
     * Array with cached data
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @var int
     */
    const MAX_IMAGE_COUNT = 10;

    /**
     * Return part of a filename
     *
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Prepare data for xml file
     *
     * @return object
     */
    abstract public function getPreparedData(): object;

    /**
     * Return host url
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->cache['host'] ?? ($this->cache['host'] = 'https://' . request()->getHost());
    }

    /**
     * Prepare string to xml format
     *
     * @param string $string
     * @return string
     */
    public function xmlSpecialChars(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_XML1 | ENT_DISALLOWED);
    }

    /**
     * Return product image urls
     *
     * @param MediaCollection $media
     * @return array
     */
    public function getProductImages(MediaCollection $media): array
    {
        return  array_slice($media->map(function ($image) {
            return $image->getUrl('full');
        })->toArray(), 0, self::MAX_IMAGE_COUNT);
    }

    /**
     * Return categories list with keys by id
     *
     * @return NestedsetCollection
     */
    public function getCategoriesList(): NestedsetCollection
    {
        if (empty($this->cache['product_categories'])) {
            $this->cache['product_categories'] = Category::all()->keyBy('id');
        }
        return $this->cache['product_categories'];
    }

}
