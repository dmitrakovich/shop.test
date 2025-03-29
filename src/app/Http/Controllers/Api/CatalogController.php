<?php

namespace App\Http\Controllers\Api;

use App\Events\Analytics\ProductView;
use App\Facades\Sale;
use App\Helpers\UrlHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Info\InstallmentResource;
use App\Http\Resources\Product\CatalogProductCollection;
use App\Http\Resources\Product\CatalogProductResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogService;
use App\Services\FeedbackService;
use App\Services\FilterService;
use App\Services\GoogleTagManagerService;
use App\Services\ProductService;
use App\Services\Seo\CatalogSeoService;
use App\Services\Seo\ProductSeoService;
use App\Services\SliderService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(
        FilterRequest $filterRequest,
        GoogleTagManagerService $gtmService,
        CatalogService $catalogService,
        // CatalogSeoService $seoService,
    ): JsonResponse {
        if ($promocode = $filterRequest->get('promocode')) {
            Sale::applyPromocode($promocode);
        }

        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        $currentCity = $filterRequest->getCity();
        $searchQuery = $filterRequest->input('search');
        UrlHelper::setCurrentFilters($currentFilters);
        UrlHelper::setCurrentCity($currentCity);

        $products = $catalogService->getProductsWithPagination($currentFilters, $sort, $searchQuery);

        $sortingList = [
            'rating' => 'по популярности',
            'newness' => 'новинки',
            'price-up' => 'по возрастанию цены',
            'price-down' => 'по убыванию цены',
        ];

        $category = end($currentFilters[Category::class])->getFilterModel();
        $badges = $catalogService->getFilterBadges($currentFilters, $searchQuery);

        $gtmService->setForCatalog($products, $category, $searchQuery);

        $data = [
            'products' => new CatalogProductCollection($products),
            'category' => $category,
            'currentFilters' => $currentFilters,
            'badges' => $badges,
            'filters' => app(FilterService::class)->getAll(),
            'sort' => $sort,
            'sortingList' => $sortingList,
            'searchQuery' => $searchQuery,
        ];

        // if ($products->isEmpty()) {
        //     $data['simpleSliders'] = app(SliderService::class)->getFormattedSimple();
        // }
        // $seoService
        //     ->setCurrentFilters($currentFilters)
        //     ->setCurrentCity($currentCity)
        //     ->setProducts($products)
        //     ->generate();

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Product $product,
        // ProductService $productService,
        SliderService $sliderService,
        // ProductSeoService $seoService,
        FeedbackService $feedbackService,
    ): array {
        // $productService->addToRecent($product->id);

        // $seoService->setProduct($product)->generate(); // !!!

        event(new ProductView($product));

        return [
            'breadcrumbs' => Breadcrumbs::generate('product', $product),
            'product' => new ProductResource($product),
            'feedbacks' => $feedbackService->getForProduct($product->id),
            'similarProducts' => CatalogProductResource::collection($sliderService->getSimilarProducts($product->id)),
            'productGroup' => CatalogProductResource::collection($product->productsFromGroup),
            // 'recentProductsSlider' => $sliderService->getRecentProducts($productService),
            'installment' => new InstallmentResource($product),
        ];
    }
}
