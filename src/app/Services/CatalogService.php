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
use App\Pagination\CatalogLengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogService
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

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
     * @param  CatalogLengthAwarePaginator<int, Product>  $products
     * @param  array<string, array<string, Url>>  $filters
     */
    protected function addTopProducts(CatalogLengthAwarePaginator $products, array $filters): void
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
     * @param  CatalogLengthAwarePaginator<int, Product>  $products
     */
    protected function topProductsCount(CatalogLengthAwarePaginator $products): int
    {
        return max(0, $products->count() - $products->perPage());
    }

    /**
     * @param  CatalogLengthAwarePaginator<int, Product>  $products
     * @param  Builder<Product>  $productsQuery
     */
    protected function addMinMaxPrices(CatalogLengthAwarePaginator $products, Builder $productsQuery): void
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
     * @param  CatalogLengthAwarePaginator<int, Product>|Collection<int, Product>  $products
     */
    protected function addGtmData(CatalogLengthAwarePaginator|Collection $products): void
    {
        $products->each(function (Product $product) {
            $product->dataLayer = GoogleTagManagerService::prepareProduct($product);
        });
    }
}
