<?php

namespace App\Services;

use App\Facades\Currency;
use App\Helpers\UrlHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributes\Top;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Laravie\SerializesQuery\Eloquent;

class CatalogService
{
    /**
     * Number of products per page
     */
    protected const PAGE_SIZE = 12;

    /**
     * @var ProductService
     */
    private $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function getProducts(array $filters, string $sort, ?string $search = null)
    {
        $productsQuery = $this->productService
            ->applyFilters($filters)
            ->search($search)
            ->sorting($sort);

        $products = $productsQuery->cursorPaginate(self::PAGE_SIZE);
        $this->addTopProducts($products, $filters);
        $products->totalCount = $productsQuery->count() + $this->topProductsCount($products);

        // save query in cache (1 hour)
        Cache::put($this->getQueryCacheKey(), Eloquent::serialize($productsQuery), 3600);

        $this->productService->addEager($products);
        $this->addMinMaxPrices($products, $productsQuery);
        $this->addGtmData($products);

        return $products;
    }

    public function getFilterBadges(?array $currentFiltersGroups, ?string $searchQuery = null): array
    {
        $badges = [];
        if (!empty($currentFiltersGroups)) {
            foreach ($currentFiltersGroups as $currentFiltersGroupKey => $currentFiltersGroup) {
                foreach ($currentFiltersGroup as $currentFilterKey => $currentFilter) {
                    $filterModel = $currentFilter->filters;
                    if ($filterModel->isInvisible() || $filterModel->slug === 'catalog' || ($currentFiltersGroupKey == Category::class) && ($currentFilterKey != array_key_last($currentFiltersGroup))) {
                        continue;
                    }
                    $badges[] = (object)[
                        'name' => $filterModel->getBadgeName(),
                        'url' => UrlHelper::generate([], [$filterModel]),
                    ];
                }
            }
        }
        if ($searchQuery) {
            $badges[] = (object)[
                'name' => 'Поиск: ' . mb_strimwidth($searchQuery, 0, 12, '...'),
                'url' => UrlHelper::generate([], [['param' => 'search']]),
            ];
        }

        return $badges;
    }

    /**
     * load next products
     *
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function getNextProducts()
    {
        $productsQuery = Cache::get($this->getQueryCacheKey());

        abort_if(empty($productsQuery), 419, 'Query cache not found');

        try {
            $productsQuery = Eloquent::unserialize($productsQuery);
            $products = $productsQuery->cursorPaginate(self::PAGE_SIZE);
        } catch (\Throwable $th) {
            abort(419, 'Page maby expired. Error: ' . $th->getMessage());
        }

        $this->productService->addEager($products);
        $this->addGtmData($products);

        return $products;
    }

    /**
     * Generate key for set/get query cahce
     */
    protected function getQueryCacheKey(): string
    {
        return 'catalog-query-' . Session::getId();
    }

    /**
     * @param  mixed  $products
     * @return void
     */
    protected function addTopProducts($products, array $filters)
    {
        if (empty($filters[Top::class])) {
            return;
        }

        $topProductsIds = array_column($filters[Top::class], 'model_id');
        $topProducts = $this->productService->getById($topProductsIds);
        if (empty($topProducts)) {
            return;
        }

        $topProducts = $topProducts->keyBy('id');
        $sorting = array_reverse($topProductsIds);
        foreach ($sorting as $productId) {
            if (isset($topProducts[$productId])) {
                $products->prepend($topProducts[$productId]);
            }
        }
    }

    /**
     * @param  mixed  $products
     */
    protected function topProductsCount($products): int
    {
        return $products->count() - self::PAGE_SIZE;
    }

    protected function addMinMaxPrices(CursorPaginator $products, Builder $productsQuery): void
    {
        $priceQuery = clone $productsQuery;
        $query = $priceQuery->getQuery();
        $bindings = $priceQuery->getBindings();
        $bindkey = 0;

        foreach ($query->wheres as $key => $where) {
            if ($where['type'] === 'Basic') {
                $bindkey++;
            } else {
                continue;
            }
            // match ($where['type']) {
            //     'Basic' => $bindkey++,
            //     'Column' => $bindkey,
            //     'In' => $bindkey += count($where['values']),
            //     'Exists', 'Nested' => $bindkey += count($where['query']->getBindings()),
            // };
            if (isset($where['column']) && $where['column'] === 'price') {
                unset($bindings[$bindkey - 1]);
                unset($query->wheres[$key]);
            }
        }
        $query->wheres = array_values($query->wheres);
        $priceQuery->setBindings(array_values($bindings));

        $products->minPrice = Currency::convert($priceQuery->min('price') ?? 0);
        $products->maxPrice = Currency::convert($priceQuery->max('price') ?? 999);
    }

    /**
     * Add GTM data to products
     *
     * @param  Collection  $products
     */
    protected function addGtmData($products): void
    {
        $products->each(function (Product $product) {
            $product->dataLayer = GoogleTagManagerService::prepareProduct($product);
        });
    }
}
