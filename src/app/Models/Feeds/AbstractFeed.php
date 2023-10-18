<?php

namespace App\Models\Feeds;

use App\Facades\Currency as CurrencyFacade;
use App\Models\Category;
use App\Models\Product;
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
    const MAX_IMAGE_COUNT = 5;

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
     * Retrieves the product media from the given MediaCollection.
     *
     * @param  MediaCollection  $media The collection of media objects.
     * @return array The array containing the images and videos.
     */
    public function getProductMedia(MediaCollection $media): array
    {
        $images = [];
        $videos = [];
        foreach ($media as $image) {
            if ($image->hasCustomProperty('video')) {
                $videos[] = $image->getCustomProperty('video');
            } else {
                $images[] = $image->getUrl('full');
            }
        }

        return [
            'images' => array_slice($images, 0, self::MAX_IMAGE_COUNT),
            'videos' => $videos,
        ];
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

    /**
     * Generates the description for a product.
     *
     * @param  Product  $product The product to generate the description for.
     * @return string The generated description for the product.
     */
    public function getDescription(Product $product): string
    {
        $currentCurrency = CurrencyFacade::getCurrentCurrency();
        $discount = ($product->old_price > 0) ? round(($product->old_price - $product->price) / $product->old_price * 100) : 0;
        $description = $product->category->name . ' ' . $product->brand->name;
        $description .= match (true) {
            ($discount >= 10) => " со скидкой {$discount}%. ",
            ($discount < 10) => ' - новинка. ',
        };
        $description .= (!empty($product->sizes) && ((count($product->sizes) > 1) || !($product->sizes?->first()?->id == 1))) ? 'Размеры: ' . $product->sizes->implode('name', ',') . '. ' : '';
        $description .= $product->fabric_top_txt ? "Материал - {$product->fabric_top_txt}. " : '';
        $description .= "Цена {$product->getPrice()} {$currentCurrency->symbol}. ";

        return $this->xmlSpecialChars(trim($description));
    }
}
