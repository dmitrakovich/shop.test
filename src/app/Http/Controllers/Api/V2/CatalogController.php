<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\Product\ProductSort;
use App\Facades\Sale;
use App\Helpers\UrlHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Ads\BannerResource;
use App\Http\Resources\Product\CatalogProductCollection;
use App\Http\Resources\Product\CategoryResource;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Category;
use App\Repositories\BannerRepository;
use App\Services\CatalogService;
use App\Services\Elasticsearch\CatalogFacetService;
use App\Services\Elasticsearch\CatalogSearchService;
use App\Services\Seo\CatalogSeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Elasticsearch catalog for the test / next frontend.
 *
 * This contract is independent from the legacy API v1 catalog.
 */
class CatalogController extends Controller
{
    public function index(
        FilterRequest $filterRequest,
        CatalogSearchService $catalogSearchService,
        CatalogFacetService $catalogFacetService,
        CatalogService $catalogService,
        CatalogSeoService $seoService,
        BannerRepository $bannerRepository,
    ): JsonResponse {
        if ($promocode = $filterRequest->get('promocode')) {
            Sale::applyPromocode($promocode);
        }

        $currentFilters = $filterRequest->getFilters();
        if (!$catalogSearchService->supportsFilters($currentFilters)) {
            return response()->json([
                'message' => 'Selected filters are not supported by Catalog API v2 yet.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sort = $filterRequest->getSorting();
        $perPage = (int)$filterRequest->input('per_page');
        $search = $filterRequest->input('search');
        $searchQuery = is_string($search) ? $search : null;

        UrlHelper::setCurrentFilters($currentFilters);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $result = $catalogSearchService->search(
            $currentFilters,
            $sort,
            $searchQuery,
            $page,
            min(max($perPage, 12), 100),
        );
        $products = $catalogService->paginateFromSearchResult(
            $result,
            $perPage,
            $page,
        );

        /** @var Category $category */
        $category = end($currentFilters[Category::class])->getFilterModel();
        $category->loadParentCategoryChain();

        $seoService
            ->setCurrentFilters($currentFilters)
            ->setProducts($products)
            ->generate();

        return response()->json([
            'products' => new CatalogProductCollection($products),
            'banners' => BannerResource::collection($bannerRepository->getCatalogBanners()),
            'category' => new CategoryResource($category),
            'facets' => $catalogFacetService->build($currentFilters, $result),
            'sort' => [
                'value' => $sort->value,
                'options' => ProductSort::options(),
            ],
            'meta' => [
                'title' => SeoFacade::getTitle(),
                'description' => SeoFacade::getDescription(),
                'url' => SeoFacade::getUrl(),
                'h1' => SeoFacade::getH1(),
                'image' => SeoFacade::getImage(),
            ],
        ]);
    }
}
