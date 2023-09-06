<?php

namespace App\Models\Feeds;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Kalnoy\Nestedset\Collection as NestedsetCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

abstract class AbstractFeed
{
    /**
     * Array with cached data
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @var string
     */
    const FILE_TYPE = 'xml';

    /**
     * @var int
     */
    const MAX_IMAGE_COUNT = 10;

    /**
     * Return part of a filename
     */
    abstract public function getKey(): string;

    /**
     * Prepare data for xml file
     */
    abstract public function getPreparedData(): object;

    /**
     * Return host url
     */
    public function getHost(): string
    {
        return $this->cache['host'] ?? ($this->cache['host'] = 'https://' . request()->getHost());
    }

    /**
     * Prepare string to xml format
     */
    public function xmlSpecialChars(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_XML1 | ENT_DISALLOWED);
    }

    /**
     * Return product image urls
     */
    public function getProductImages(MediaCollection $media): array
    {
        return array_slice($media->map(function ($image) {
            return $image->getUrl('full');
        })->toArray(), 0, self::MAX_IMAGE_COUNT);
    }

    /**
     * Return categories list with keys by id
     */
    public function getCategoriesList(): NestedsetCollection
    {
        if (empty($this->cache['product_categories'])) {
            $this->cache['product_categories'] = Category::all()->keyBy('id');
        }

        return $this->cache['product_categories'];
    }

    /**
     * Prepare sizes string from sizes list
     */
    protected function sizesToString(EloquentCollection $sizes): string
    {
        $sizesList = $sizes->pluck('name');
        $sizesStr = 'Размеры: ' . ($sizesList[0] ?? 'без размера');

        $useDash = false;
        $sizesListCount = count($sizesList);
        for ($i = 1; $i < $sizesListCount; $i++) {
            if (
                ($i + 1) < $sizesListCount
                && ((int)$sizesList[$i] - 1) == $sizesList[$i - 1]
                && ((int)$sizesList[$i] + 1) == $sizesList[$i + 1]
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
