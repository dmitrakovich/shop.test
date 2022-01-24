<?php

namespace App\Models\Xml;

use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
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
                    'google_product_category' => $this->getGoogleCategory($item->category),
                    'product_type' => $this->getProductType($item->category),
                    'description' => $this->getDescription($item),
                    'title' => $this->xmlSpecialChars($item->extendedName()),
                    'material' => $item->fabric_top_txt,
                    'color' => $this->getColor($item->colors),
                ];
            })->toArray();
    }

    /**
     * Return google product category
     *
     * @see https://support.google.com/merchants/answer/6324436?hl=ru
     * @param Category $category
     * @return integer
     */
    protected function getGoogleCategory(Category $category): int
    {
        if ($category->id == 28) {
            return 100;
        } elseif ($category->parent_id == Category::ACCESSORIES_PARENT_ID) {
            return 3032;
        } else {
            return 187;
        }
    }

    /**
     * Generate & return product type
     *
     * @param Category $category
     * @return string
     */
    protected function getProductType(Category $category): string
    {
        $type = ['Женщинам'];
        if ($category->parent_id == Category::ACCESSORIES_PARENT_ID) {
            $type[] = 'Женские аксессуары';
        } else {
            $type[] = 'Женская обувь';
            if ($category->parent_id != Category::ROOT_CATEGORY_ID) {
                $type[] = $this->getCategoriesList()[$category->parent_id]->title;
            }
        }
        $type[] = $category->title;
        return implode(' > ', $type);
    }

    /**
     * Prepare color from colors for filters
     *
     * @param EloquentCollection $colors
     * @return string
     */
    public function getColor(EloquentCollection $colors): string
    {
        return count($colors) == 1 ? $colors[0]->name : 'разноцветный';
    }

    /**
     * Generate product description
     *
     * @param Product $product
     * @return string
     */
    public function getDescription(Product $product): string
    {
        // $this->prepareSizes($item->sizes)
        return 'product description';
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
