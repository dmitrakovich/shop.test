<?php

namespace App\Services;

use App\Contracts\Filterable;
use App\Enums\Product\ProductSort;
use App\Facades\Currency;
use App\Helpers\UrlHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributes\Top;
use App\Models\Url;
use App\Pagination\CatalogCursorPaginator;
use App\Pagination\CatalogLengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * @param  array<string, array<string, Url>>  $filters
     * @return CatalogCursorPaginator<int, Product>
     */
    public function getProducts(array $filters, ProductSort $sort, ?string $search = null): CatalogCursorPaginator
    {
        $productsQuery = $this->productService
            ->applyFilters($filters)
            ->search($search)
            ->sorting($sort, $filters);

        $products = CatalogCursorPaginator::fromPaginator($productsQuery->cursorPaginate(self::PAGE_SIZE));
        $this->addTopProducts($products, $filters);
        $products->totalCount = $productsQuery->count() + $this->topProductsCount($products);

        // save query in cache (1 hour)
        Cache::put($this->getQueryCacheKey(), Eloquent::serialize($productsQuery), 3600);

        $this->productService->addEager($products);
        $this->addMinMaxPrices($products, $productsQuery);
        $this->addGtmData($products);

        return $products;
    }

    /**
     * @param  array<string, array<string, Url>>  $filters
     * @return CatalogLengthAwarePaginator<int, Product>
     */
    public function getProductsWithPagination(array $filters, ProductSort $sort, ?string $search = null, ?int $perPage = 12): CatalogLengthAwarePaginator
    {
        /** @var Builder<Product> $productsQuery */
        $productsQuery = $this->productService
            ->applyFilters($filters)
            ->search($search)
            ->sorting($sort, $filters);

        $perPage = min(max($perPage, 12), 100);
        $products = CatalogLengthAwarePaginator::fromPaginator($productsQuery->paginate($perPage));
        $this->addTopProducts($products, $filters);
        $products->totalCount = $products->total() + $this->topProductsCount($products);

        $this->productService->addEager($products);
        $this->addMinMaxPrices($products, $productsQuery);
        $this->addGtmData($products);

        return $products;
    }

    /**
     * @param  array<string, array<string, Url>>  $currentFiltersGroups
     * @return list<object{name: string, url: string}>
     */
    public function getFilterBadges(array $currentFiltersGroups = [], ?string $searchQuery = null): array
    {
        $badges = [];
        foreach ($currentFiltersGroups as $filterModel => $currentFiltersGroup) {
            if ($filterModel === Category::class) {
                $currentFiltersGroup = [end($currentFiltersGroup)];
            }
            foreach ($currentFiltersGroup as $currentFilter) {
                $filter = $currentFilter->filters;
                if (!$filter instanceof Filterable || $filter->isInvisible()) {
                    continue;
                }
                $badges[] = (object)[
                    'name' => $filter->getBadgeName(),
                    'url' => UrlHelper::generate([], [$filter]),
                ];
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
     * @return CatalogCursorPaginator<int, Product>
     */
    public function getNextProducts(): CatalogCursorPaginator
    {
        $productsQuery = Cache::get($this->getQueryCacheKey());

        abort_if(empty($productsQuery), 419, 'Query cache not found');

        try {
            /** @var Builder<Product> $productsQuery */
            $productsQuery = Eloquent::unserialize($productsQuery);
            $products = CatalogCursorPaginator::fromPaginator($productsQuery->cursorPaginate(self::PAGE_SIZE));
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
     * @param  CatalogCursorPaginator<int, Product>|CatalogLengthAwarePaginator<int, Product>  $products
     * @param  array<string, array<string, Url>>  $filters
     */
    protected function addTopProducts(CatalogCursorPaginator|CatalogLengthAwarePaginator $products, array $filters): void
    {
        if (empty($filters[Top::class])) {
            return;
        }

        $topProductsIds = array_column($filters[Top::class], 'model_id');
        $topProducts = $this->productService->getById($topProductsIds);
        if ($topProducts->isEmpty()) {
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
     * @param  CatalogCursorPaginator<int, Product>|CatalogLengthAwarePaginator<int, Product>  $products
     */
    protected function topProductsCount(CatalogCursorPaginator|CatalogLengthAwarePaginator $products): int
    {
        return $products->count() - self::PAGE_SIZE;
    }

    /**
     * @param  CatalogCursorPaginator<int, Product>|CatalogLengthAwarePaginator<int, Product>  $products
     * @param  Builder<Product>  $productsQuery
     */
    protected function addMinMaxPrices(CatalogCursorPaginator|CatalogLengthAwarePaginator $products, Builder $productsQuery): void
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
     * @param  CatalogCursorPaginator<int, Product>|CatalogLengthAwarePaginator<int, Product>|Collection<int, Product>  $products
     */
    protected function addGtmData(CatalogCursorPaginator|CatalogLengthAwarePaginator|Collection $products): void
    {
        $products->each(function (Product $product) {
            $product->dataLayer = GoogleTagManagerService::prepareProduct($product);
        });
    }
}
