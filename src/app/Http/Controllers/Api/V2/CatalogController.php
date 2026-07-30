<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\Product\ProductSort;
use App\Facades\Sale;
use App\Helpers\UrlHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Ads\BannerResource;
use App\Http\Resources\Product\CatalogProductCollection;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Category;
use App\Repositories\BannerRepository;
use App\Services\CatalogService;
use App\Services\Elasticsearch\CatalogSearchService;
use App\Services\FilterService;
use App\Services\GoogleTagManagerService;
use App\Services\Seo\CatalogSeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Elasticsearch catalog for the test / next frontend.
 *
 * Top-level payload matches API v1 (products, banners, meta, …).
 * `filters` may diverge later (facet counts); for now same dictionary as v1.
 */
class CatalogController extends Controller
{
    public function index(
        FilterRequest $filterRequest,
        CatalogSearchService $catalogSearchService,
        CatalogService $catalogService,
        CatalogSeoService $seoService,
        GoogleTagManagerService $gtmService,
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
        $currentCity = $filterRequest->getCity();
        $search = $filterRequest->input('search');
        $searchQuery = is_string($search) ? $search : null;

        UrlHelper::setCurrentFilters($currentFilters);
        UrlHelper::setCurrentCity($currentCity);

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

        $category = end($currentFilters[Category::class])->getFilterModel();
        $badges = $catalogService->getFilterBadges($currentFilters, $searchQuery);

        $gtmService->setForCatalog($products, $category, $searchQuery);

        $seoService
            ->setCurrentFilters($currentFilters)
            ->setCurrentCity($currentCity)
            ->setProducts($products)
            ->generate();

        return response()->json([
            'products' => new CatalogProductCollection($products),
            'banners' => BannerResource::collection($bannerRepository->getCatalogBanners()),
            'category' => $category,
            'currentFilters' => $currentFilters,
            'badges' => $badges,
            // May later become facet aggs with counts; until then same as v1.
            'filters' => app(FilterService::class)->getAll(),
            'sort' => $sort->value,
            'sortingList' => ProductSort::options(),
            'searchQuery' => $searchQuery,
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
