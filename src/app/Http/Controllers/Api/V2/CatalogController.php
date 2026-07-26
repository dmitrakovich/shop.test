<?php

namespace App\Http\Controllers\Api\V2;

use App\ElasticSearch\Indices\Product\Queries\CatalogQuery;
use App\Enums\Product\ProductSort;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\CatalogProductResource;
use App\Repositories\Catalog\CatalogDictionaryRepository;
use App\Sections\Catalog\Pipelines\FilterParsingPipeline;
use App\Sections\Catalog\Services\FilterPreparationService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * @param  CatalogDictionaryRepository  $dictionaryRepository  Facet dictionaries
     * @param  FilterParsingPipeline  $filterParsingPipeline  Filter parser
     * @param  FilterPreparationService  $filterPreparationService  Facet preparation
     * @param  ProductService  $productService  Product loader
     */
    public function __construct(
        private readonly CatalogDictionaryRepository $dictionaryRepository,
        private readonly FilterParsingPipeline $filterParsingPipeline,
        private readonly FilterPreparationService $filterPreparationService,
        private readonly ProductService $productService,
    ) {}

    /**
     * Return Elasticsearch-powered catalog page.
     *
     * @param  Request  $request  HTTP request
     * @param  string|null  $path  Filter path after /catalog/
     * @return JsonResponse Catalog response
     */
    public function index(Request $request, ?string $path = null): JsonResponse
    {
        if (!config('services.search.enabled')) {
            return response()->json([
                'message' => 'Elasticsearch disabled',
                'total' => 0,
                'filter_tags' => [],
                'active_filters_count' => 0,
                'filters' => (object)[],
                'sort_list' => [],
                'sortingList' => ProductSort::options(),
                'pagination' => [],
                'products' => [],
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $dictionaries = $this->dictionaryRepository->getDictionaries();
        $filterState = $this->filterParsingPipeline->parse($request, $dictionaries);

        /** @var CatalogQuery $query */
        $query = app(CatalogQuery::class);
        $query
            ->setPageSize((int)config('catalog.page_size', 12))
            ->setSearch($filterState->search)
            ->setFilters($filterState->getElasticFilters())
            ->setPage($filterState->page)
            ->setSort($filterState->sort ?? ProductSort::default()->value);

        $elasticResult = $query->search()->getSearchResult();
        $prepared = $this->filterPreparationService->prepareFilters(
            $elasticResult['buckets'] ?? [],
            $dictionaries,
            $filterState
        );

        $pagination = $elasticResult['pagination'] ?? null;
        $paginationPayload = $pagination instanceof LengthAwarePaginator
            ? $pagination->toArray()
            : (array)($pagination ?? []);

        $elasticProducts = $elasticResult['products'] ?? [];
        $productIds = array_values(array_filter(array_map(
            fn (mixed $product): int => (int)(((object)$product)->id ?? 0),
            $elasticProducts,
        )));

        $products = $this->productService->getById($productIds)
            ->sortBy(fn ($product) => array_search($product->id, $productIds, true))
            ->values();

        return response()->json([
            'total' => (int)($elasticResult['total'] ?? 0),
            'filter_tags' => $filterState->filterTags,
            'active_filters_count' => count($filterState->filterTags),
            'filters' => $prepared->filters,
            'sort_list' => $prepared->sort_list,
            'sortingList' => ProductSort::options(),
            'pagination' => $paginationPayload,
            'products' => CatalogProductResource::collection($products),
        ]);
    }
}
