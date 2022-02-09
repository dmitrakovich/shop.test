<?php

namespace App\Services;

use App\Models\ProductAttributes\Top;
use Illuminate\Support\Facades\Cache;
use Laravie\SerializesQuery\Eloquent;
use Illuminate\Support\Facades\Session;

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
     * @param array $filters
     * @param string $sort
     * @param string|null $search
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function getProducts(array $filters, string $sort, ?string $search = null)
    {
        $productsQuery = $this->productService->getForCatalog(
            $filters, $sort, $search
        );

        $products = $productsQuery->cursorPaginate(self::PAGE_SIZE);
        $this->addTopProducts($products, $filters);
        $products->totalCount = $productsQuery->count() + $this->topProductsCount($products);

        // save query in cache (1 hour)
        Cache::put($this->getQueryCacheKey(), Eloquent::serialize($productsQuery), 3600);

        return $products;
    }

    public function getNextProducts()
    {
        $productsQuery = Cache::get($this->getQueryCacheKey());

        abort_if(empty($productsQuery), 419, 'Query cache not found');

        $productsQuery = Eloquent::unserialize($productsQuery);
        return $productsQuery->cursorPaginate(self::PAGE_SIZE);
    }

    /**
     * Generate key for set/get query cahce
     *
     * @return string
     */
    protected function getQueryCacheKey(): string
    {
        return 'catalog-query-' . Session::getId();
    }

    /**
     * @param mixed $products
     * @param array $filters
     * @return void
     */
    protected function addTopProducts($products, array $filters)
    {
        if (empty($filters[Top::class])) {
            return;
        }

        $topProducts = $this->productService->getById($filters[Top::class]);
        if (empty($topProducts)) {
            return;
        }

        $topProducts = $topProducts->keyBy('id');
        $sorting = array_reverse(array_column($filters[Top::class], 'model_id'));
        foreach ($sorting as $productId) {
            if (isset($topProducts[$productId])) {
                $products->prepend($topProducts[$productId]);
            }
        }
    }

    /**
     * @param mixed $products
     * @return integer
     */
    protected function topProductsCount($products): int
    {
        return $products->count() - self::PAGE_SIZE;
    }
}
