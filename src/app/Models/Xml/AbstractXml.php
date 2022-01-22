<?php

namespace App\Models\Xml;

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
}
