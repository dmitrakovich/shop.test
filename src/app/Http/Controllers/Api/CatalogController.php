<?php

namespace App\Http\Controllers\Api;

use App\Enums\Product\ProductSort;
use App\Events\Analytics\ProductView;
use App\Facades\Device;
use App\Helpers\UrlHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\SearchSuggestionsRequest;
use App\Http\Resources\Ads\BannerResource;
use App\Http\Resources\Feedback\FeedbackResource;
use App\Http\Resources\Info\InstallmentResource;
use App\Http\Resources\Product\CatalogProductCollection;
use App\Http\Resources\Product\CatalogProductResource;
use App\Http\Resources\Product\CategoryResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Search\SearchSuggestionsResource;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Category;
use App\Models\Logs\SearchQueryLog;
use App\Models\Product;
use App\Repositories\BannerRepository;
use App\Services\CatalogService;
use App\Services\Elasticsearch\CatalogFacetService;
use App\Services\Elasticsearch\CatalogSearchService;
use App\Services\FeedbackService;
use App\Services\SearchSuggestionsService;
use App\Services\Seo\CatalogSeoService;
use App\Services\SliderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    /**
     * Elasticsearch storefront catalog (served under /api/v2/catalog).
     */
    public function index(
        FilterRequest $filterRequest,
        CatalogSearchService $catalogSearchService,
        CatalogFacetService $catalogFacetService,
        CatalogService $catalogService,
        CatalogSeoService $seoService,
        BannerRepository $bannerRepository,
    ): JsonResponse {
        $currentFilters = $filterRequest->getFilters();
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

        if ($page === 1 && $catalogSearchService->normalizeSearchQuery($searchQuery) !== '') {
            SearchQueryLog::query()->create([
                'device_id' => Device::current()->id,
                'query' => mb_substr(Str::squish(mb_strtolower((string)$searchQuery)), 0, 255),
                'results_count' => $result->total,
                'filters_path' => mb_substr((string)$filterRequest->route('path'), 0, 255) ?: null,
            ]);
        }

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

    /**
     * Empty field: popular products and top queries.
     * From 2 characters: text suggestions and matching categories.
     */
    public function suggestions(
        SearchSuggestionsRequest $request,
        SearchSuggestionsService $searchSuggestionsService,
    ): SearchSuggestionsResource {
        return new SearchSuggestionsResource(
            $searchSuggestionsService->forQuery($request->string('query')->value()),
        );
    }

    /**
     * Display the specified resource (served under /api/v1/product).
     */
    public function show(
        Product $product,
        SliderService $sliderService,
        FeedbackService $feedbackService,
    ): array {
        event(new ProductView($product));

        return [
            'breadcrumbs' => [], // todo: remove after remove on frontend
            'product' => new ProductResource($product),
            'feedbacks' => FeedbackResource::collection($feedbackService->getForProduct($product->id)),
            'similarProducts' => CatalogProductResource::collection($sliderService->getSimilarProducts($product->id)),
            'productGroup' => CatalogProductResource::collection($product->productsFromGroup),
            'installment' => new InstallmentResource($product),
        ];
    }
}
