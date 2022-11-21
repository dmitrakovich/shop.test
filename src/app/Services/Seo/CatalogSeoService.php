<?php

namespace App\Services\Seo;

use App\Models\{
    Brand,
    Category,
    Collection,
    Color,
    Fabric,
    Heel,
    Season,
    Size,
    Style,
    Tag
};
use App\Models\ProductAttributes\Status;
use App\Models\ProductAttributes\Price;
use App\Helpers\UrlHelper;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Str;
use App\Libraries\Seo\Facades\SeoFacade;

class CatalogSeoService
{
    private array $currentFilters = [];
    private CursorPaginator $catalogProducts;
    const MAX_FILTERS_COUNT = 3;

    const MAX_FILTER_VALUES_COUNT = 1;


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
     * Set current filters
     */
    public function setCurrentFilters(array $currentFilters): self
    {
        $this->currentFilters = $currentFilters;
        return $this;
    }

    /**
     * Set catalog products
     */
    public function setProducts(CursorPaginator $products): self
    {
        $this->catalogProducts = $products;
        return $this;
    }

    /**
     * Generate title for catalog
     */
    public function getCatalogTitle(): string
    {
        $currentFilters = $this->currentFilters;
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
    public function getCatalogDescription(): string
    {
        $currentFilters = $this->currentFilters;
        return $this->getCatalogTitle($currentFilters) . ' с примеркой по Беларуси';
    }

    /**
     * Get catalog canonical url
     */
    public function getCatalogCanonicalUrl(): string
    {
        $canonicalUrl = UrlHelper::generate();
        return $canonicalUrl;
    }

    /**
     * Prepare meta info for robots
     */
    public function metaForRobotsForCatalog(): string
    {
        $currentFilters = $this->currentFilters;
        $filtersCount = 0;
        foreach ($currentFilters as $filterType => $filters) {
            if($filterType === Price::class) {
                foreach($filters as $filterKey => $filter) {
                    if(str_contains($filterKey, 'price-from')) {
                        return 'noindex, follow';
                    }
                }
            }
            $filterValuesCount = intval($filterType === Category::class) ?: count($filters);
            $filtersCount += $filterValuesCount;

            if ($filtersCount > self::MAX_FILTERS_COUNT || $filterValuesCount > self::MAX_FILTER_VALUES_COUNT) {
                return 'noindex, nofollow';
            }
        }

        return 'all';
    }

    /**
     * Generate catalog seo
     */
    public function generate(): void
    {
        if (!$this->catalogProducts->isNotEmpty()) {
            SeoFacade::setRobots('noindex, nofollow');
        } else {
            SeoFacade::setImage($this->catalogProducts->first()->getFirstMedia()->getUrl('catalog'));

            $canonicalUrl = trim($this->getCatalogCanonicalUrl(), '/');
            $currentPath  = request()->path();
            if ($canonicalUrl !== $currentPath) {
                SeoFacade::setRobots('noindex, follow');
            } else {
                SeoFacade::setRobots($this->metaForRobotsForCatalog());
            }
            SeoFacade::setTitle($this->getCatalogTitle())
                ->setDescription($this->getCatalogDescription())
                ->setUrl($canonicalUrl);
        }
    }
}
