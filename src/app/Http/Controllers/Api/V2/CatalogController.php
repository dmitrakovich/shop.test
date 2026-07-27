<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\Product\ProductSort;
use App\Facades\Sale;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Product\CatalogProductResource;
use App\Services\CatalogService;
use App\Services\Elasticsearch\CatalogSearchService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Elasticsearch catalog for the test / next frontend.
 * Response shape is intentionally independent of API v1 (MySQL).
 */
class CatalogController extends Controller
{
    public function index(
        FilterRequest $filterRequest,
        CatalogSearchService $catalogSearchService,
        CatalogService $catalogService,
        ProductService $productService,
    ): JsonResponse {
        if ($promocode = $filterRequest->get('promocode')) {
            Sale::applyPromocode($promocode);
        }

        $filters = $filterRequest->getFilters();
        if (!$catalogSearchService->supportsFilters($filters)) {
            return response()->json([
                'message' => 'Selected filters are not supported by Catalog API v2 yet.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sort = $filterRequest->getSorting();
        $perPage = min(max((int)$filterRequest->input('per_page', 12), 12), 100);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $search = $filterRequest->input('search');
        $searchQuery = is_string($search) ? $search : null;

        $result = $catalogSearchService->search($filters, $sort, $searchQuery, $page, $perPage);

        $order = array_flip($result->productIds);
        $products = $productService->getById($result->productIds)
            ->sortBy(fn ($product): int => $order[$product->id] ?? PHP_INT_MAX)
            ->values();

        return response()->json([
            'total' => $result->total,
            'page' => $page,
            'per_page' => $perPage,
            'min_price' => $result->minPrice,
            'max_price' => $result->maxPrice,
            'sort' => $sort->value,
            'sorting_list' => ProductSort::options(),
            'search' => $searchQuery,
            'badges' => $catalogService->getFilterBadges($filters, $searchQuery),
            'products' => CatalogProductResource::collection($products),
        ]);
    }
}
