<?php

namespace App\Models\Feeds;

use App\Models\Product;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class GoogleCsv extends AbstractFeed
{
    /**
     * @var string
     */
    const FILE_TYPE = 'csv';

    /**
     * @var int
     */
    const DESCRIPTION_MAX_WIDTH = 5000;

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
     * Prepare data for csv file
     *
     * @return object
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
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        return [
            'title' => 'Барокко',
            'link' => $this->getHost(),
            'description' => 'Интернет магазин брендовой обуви',
        ];
    }

    /**
     * Rows data
     *
     * @return array
     */
    protected function getRows(): array
    {
        return [
            [ '100','2','43'],
            [ '1','32','3dfg'],
            [ '12','2','dfg3'],
            [ '1','42','fgh3'],
        ];

        /*return (new ProductService)->getForXml(true)
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
            })->toArray();*/
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
            if (!in_array($category->parent_id, [null, Category::ROOT_CATEGORY_ID])) {
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
        $description = $product->extendedName() . '. ';
        $description .= $this->sizesToString($product->sizes) . '. ';
        $description .= "Цвет: {$product->color_txt}. ";

        if (!empty($product->fabric_top_txt)) {
            $description .= 'Материал';
            if ($product->category->parent_id != Category::ACCESSORIES_PARENT_ID) {
                $description .= ' верха';
            }
            $description .= ": {$product->fabric_top_txt}. ";
        }

        if (!empty($product->fabric_insole_txt)) {
            $description .= "Материал подкладки: {$product->fabric_insole_txt}. ";
        }

        if (!empty($product->fabric_outsole_txt)) {
            $description .= "Материал подошвы: {$product->fabric_outsole_txt}. ";
        }

        if (!empty($product->heel_txt)) {
            $description .=  "Высота каблука: {$product->heel_txt}. ";
        }

        $description .= $product->description;

        $description = trim(strip_tags($description));
        $description = Str::limit($description, self::DESCRIPTION_MAX_WIDTH - 3, '...');

        return $description;
    }
}
