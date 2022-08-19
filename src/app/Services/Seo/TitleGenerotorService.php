<?php

namespace App\Services\Seo;

use App\Models\Heel;
use App\Models\Size;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Style;
use App\Models\Fabric;
use App\Models\Season;
use App\Models\Product;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ProductAttributes\Status;
use App\Models\Tag;
use Illuminate\Support\Str;

class TitleGenerotorService
{
    const ATTRIBUTE_PRIORITY = [
        Category::class,
        Size::class,
        Tag::class,
        Color::class,
        Fabric::class,
        Heel::class,
        Season::class,
        Status::class,
        Style::class,
        Brand::class,
        Collection::class,
    ];

    const ATTRIBUTE_ORDER = [
        Color::class,
        Fabric::class,
        Season::class,
        Style::class,
        Category::class,
        Status::class,
        Size::class,
        Tag::class,
        Collection::class,
        Heel::class,
        Brand::class,
    ];

    /**
     * Generate title for product
     */
    public function getProductTitle(Product $product): string
    {
        $discount = $product->getSalePercentage();

        return $product->extendedName() . ' ' . ($discount ? "со скидкой {$discount}%." : '- новинка!');
    }

    /**
     * Generate description for product
     */
    public function getProductDescription(Product $product): string
    {
        $description = $this->getProductTitle($product);

        if (!empty($product->color_txt)) {
            $description .= " Цвет: {$product->color_txt}.";
        }
        if ($product->sizes->isNotEmpty() && !$product->hasOneSize()) {
            $description .= ' Размеры: ' . $product->sizes->implode('name', ', ');
        }

        return $description;
    }

    /**
     * Generate title for catalog
     */
    public function getCatalogTitle(array $currentFilters): string
    {
        $emptyCategory = true;
        $titleValues = [];
        foreach (self::ATTRIBUTE_PRIORITY as $attrModel) {
            if ($attrModel === Category::class) {
                /** @var Category $category */
                $category = end($currentFilters[$attrModel])->filters;
                $titleValues[$attrModel] = $category->getNameForCatalogTitle();
                $emptyCategory = $category->isRoot();
                continue;
            }

            if (empty($currentFilters[$attrModel]) || count($currentFilters[$attrModel]) > 1) {
                continue;
            }

            $filter = reset($currentFilters[$attrModel]);

            switch ($attrModel) {
                case Color::class:
                case Fabric::class:
                case Season::class:
                case Style::class:
                    $seoFormNumber = $emptyCategory ? 1 : 3;
                    $value = explode(',', (string)$filter->filters->seo)[$seoFormNumber] ?? null;
                    break;

                case Status::class:
                    $value = $filter->filters->getForTitle();
                    break;

                case Size::class:
                    /** @var Size $size */
                    $size = $filter->filters;
                    $value = $size->slug === Size::ONE_SIZE_SLUG ? null : "в {$size->name} размере";
                    break;

                case Tag::class:
                case Heel::class:
                case Brand::class:
                    $value = $filter->filters->seo ?? $filter->filters->name;
                    break;

                case Collection::class:
                    $value = $filter->filters->name;
                    break;

                default:
                    $value = null;
                    break;
            }

            if (!empty($value)) {
                $titleValues[$attrModel] = $value;
            }

            if (count($titleValues) >= 4) {
                break;
            }
        }

        $titleValuesOrdered = [];
        foreach (self::ATTRIBUTE_ORDER as $attrModel) {
            if (isset($titleValues[$attrModel])) {
                $titleValuesOrdered[] = $titleValues[$attrModel];
            }
        }

        return Str::ucfirst((!$emptyCategory ? 'купить ' : '') . implode(' ', $titleValuesOrdered));
    }

    /**
     * Generate description for catalog
     */
    public function getCatalogDescription(array $currentFilters): string
    {
        return $this->getCatalogTitle($currentFilters) . ' с примеркой по Беларуси';
    }
}
