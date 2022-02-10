<?php

namespace App\Http\Controllers\Shop;

use App\Models\Filter;
use App\Models\Category;
use App\Helpers\UrlHelper;
use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Http\Requests\FilterRequest;
use App\Services\GoogleTagManagerService;

class CatalogController extends BaseController
{
    /**
     * Render products for next page
     *
     * @param CatalogService $catalogService
     * @param Request $request
     * @param GoogleTagManagerService $gtmService
     * @return array
     */
    public function ajaxNextPage(
        CatalogService $catalogService,
        Request $request,
        GoogleTagManagerService $gtmService
    ): array {
        $request->validate(['cursor' => 'required']);

        $products = $catalogService->getNextProducts();

        $renderedProducts = [];
        foreach ($products as $product) {
            $renderedProducts[] = view('shop.catalog-product', compact('product'))->render();
        }

        $dataLayer = $gtmService->getViewForCatalog(
            $products, $request->input('category'), $request->input('search')
        );

        return [
            'rendered_products' => $renderedProducts,
            'cursor' => optional($products->nextCursor())->encode(),
            'has_more' => $products->hasMorePages(),
            'data_layer' => $dataLayer->toArray(),
        ];
    }

    public function show(
        CatalogService $catalogService,
        FilterRequest $filterRequest,
        GoogleTagManagerService $gtmService
    ) {
        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        $searchQuery = $filterRequest->input('search');
        UrlHelper::setCurrentFilters($currentFilters);
        // dump($currentFilters);

        $products = $catalogService->getProducts($currentFilters, $sort, $searchQuery);

        $sortingList = [
            'rating' => 'по популярности',
            'newness' => 'новинки',
            'price-up' => 'по возрастанию цены',
            'price-down' => 'по убыванию цены',
        ];

        $category = end($currentFilters[Category::class])->getFilterModel();

        $gtmService->setViewForCatalog($products, $category, $searchQuery);

        return view('shop.catalog', [
            'products' => $products,
            'category' => $category,
            'currentFilters' => $currentFilters,
            'filters' => Filter::all(),
            'sort' => $sort,
            'sortingList' => $sortingList,
            'searchQuery' => $searchQuery,
        ]);
    }
}
