<?php

namespace App\Models\Feeds;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GoogleXml extends AbstractFeed
{
    /**
     * @var int
     */
    const DESCRIPTION_MAX_WIDTH = 5000;

    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'google';
    }

    /**
     * Prepare data for xml file
     */
    public function getPreparedData(): object
    {
        return (object)[
            'channel' => $this->getChannel(),
            'items' => $this->getItems(),
        ];
    }

    /**
     * Data for header
     */
    protected function getChannel(): object
    {
        return (object)[
            'title' => 'Барокко',
            'link' => $this->getHost(),
            'description' => 'Интернет магазин брендовой обуви',
        ];
    }

    /**
     * Items data
     */
    protected function getItems(): array
    {
        return (new ProductService())->getForFeed(true)
            ->map(function (Product $item) {
                $media = $this->getProductMedia($item->getMedia());

                return (object)[
                    'id' => $item->id,
                    'link' => $this->getHost() . $item->getUrl(),
                    'size' => $item->sizes->implode('name', '/'),
                    'availability' => $item->trashed() ? 'out of stock' : 'in stock',
                    'price' => $item->getPrice(),
                    'old_price' => $item->getOldPrice(),
                    'images' => $media['images'],
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
     */
    protected function getProductType(Category $category): string
    {
        $type = ['Женщинам'];
        if ($category->parent_id == Category::ACCESSORIES_PARENT_ID) {
            $type[] = 'Женские аксессуары';
        } else {
            $type[] = 'Женская обувь';
            if (!in_array($category->parent_id, [null, Category::ROOT_CATEGORY_ID, Category::SHOES_PARENT_ID])) {
                $type[] = $this->getCategoriesList()[$category->parent_id]->title;
            }
        }
        $type[] = $category->title;

        return implode(' > ', $type);
    }

    /**
     * Prepare color from colors for filters
     */
    public function getColor(EloquentCollection $colors): string
    {
        return count($colors) == 1 ? $colors[0]->name : 'разноцветный';
    }
}
