<?php

namespace App\Services;

use App\Facades\Currency;
use App\Models\Product;
use App\Pagination\CatalogLengthAwarePaginator;
use App\Services\Elasticsearch\CatalogSearchResult;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogService
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Build a catalog paginator from an Elasticsearch hit list.
     *
     * @return CatalogLengthAwarePaginator<int, Product>
     */
    public function paginateFromSearchResult(
        CatalogSearchResult $result,
        int $perPage,
        int $page,
    ): CatalogLengthAwarePaginator {
        $perPage = min(max($perPage, 12), 100);
        $order = array_flip($result->productIds);
        /** @var EloquentCollection<int, Product> $items */
        $items = $result->productIds === []
            ? new EloquentCollection()
            : Product::query()
                ->whereIn('id', $result->productIds)
                ->get()
                ->sortBy(fn (Product $product): int => $order[$product->id] ?? PHP_INT_MAX)
                ->values();

        /** @var CatalogLengthAwarePaginator<int, Product> $products */
        $products = new CatalogLengthAwarePaginator(
            $items,
            $result->total,
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );

        $products->minPrice = Currency::convert($result->minPrice);
        $products->maxPrice = Currency::convert($result->maxPrice);

        $this->productService->addEager($products);

        return $products;
    }
}
