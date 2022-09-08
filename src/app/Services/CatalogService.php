<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use App\Models\ProductAttributes\Top;
use Illuminate\Support\Facades\Cache;
use Laravie\SerializesQuery\Eloquent;
use Illuminate\Support\Facades\Session;
use App\Services\GoogleTagManagerService;
use App\Helpers\UrlHelper;

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

        $this->addGtmData($products);

        // save query in cache (1 hour)
        Cache::put($this->getQueryCacheKey(), Eloquent::serialize($productsQuery), 3600);

        return $products;
    }

    public function getFilterBadges(?array $currentFiltersGroups, ?string $searchQuery = null):array {
      $badges = [];
      if(!empty($currentFiltersGroups)) {
        foreach ($currentFiltersGroups as $currentFiltersGroup) {
          foreach ($currentFiltersGroup as $currentFilter) {
            if(isset($currentFilter->filters->slug) && $currentFilter->filters->slug !== 'catalog') {
              $badges[] = (object)[
                'name'  => $currentFilter->filters->name,
                'url'   => UrlHelper::generate([], [$currentFilter->filters])
              ];
            }
          }
        }
      }
      if($searchQuery) {
        $badges[] = (object)[
          'name'  => 'Поиск: ' . $searchQuery,
          'url'   => UrlHelper::generate([], [['param' => 'search']])
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

        $this->addGtmData($products);

        return $products;
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

    /**
     * Add GTM data to products
     *
     * @param Collection $products
     * @return void
     */
    protected function addGtmData($products): void
    {
        $products->each(function (Product $product) {
            $product->dataLayer = GoogleTagManagerService::prepareProduct($product);
        });
    }
}
