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

        return [
            'rendered_products' => $renderedProducts,
            'cursor' => optional($products->nextCursor())->encode(),
            'has_more' => $products->hasMorePages(),
            'data_layers' => $gtmService->getForCatalogArrays(
                $products, $request->input('category'), $request->input('search')
            ),
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
        $badges = $catalogService->getFilterBadges($currentFilters);

        $gtmService->setForCatalog($products, $category, $searchQuery);

        return view('shop.catalog', [
            'products' => $products,
            'category' => $category,
            'currentFilters' => $currentFilters,
            'badges' => $badges,
            'filters' => Filter::all(),
            'sort' => $sort,
            'sortingList' => $sortingList,
            'searchQuery' => $searchQuery,
        ]);
    }
}
