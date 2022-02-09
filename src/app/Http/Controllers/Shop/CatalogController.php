<?php

namespace App\Http\Controllers\Shop;

use App\Models\Filter;
use App\Models\Category;
use App\Helpers\UrlHelper;
use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Http\Requests\FilterRequest;

class CatalogController extends BaseController
{
    /**
     * Render products for next page
     *
     * @return array
     */
    public function ajaxNextPage(CatalogService $catalogService, Request $request)
    {
        $request->validate(['cursor' => 'required']);

        $products = $catalogService->getNextProducts();

        $renderedProducts = [];
        foreach ($products as $product) {
            $renderedProducts[] = view('shop.catalog-product', compact('product'))->render();
        }

        return [
            'rendered_products' => $renderedProducts,
            'cursor' => optional($products->nextCursor())->encode(),
            'has_more' => $products->hasMorePages()
        ];
    }

    public function show(CatalogService $catalogService, FilterRequest $filterRequest)
    {
        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        UrlHelper::setCurrentFilters($currentFilters);
        // dump($currentFilters);

        $products = $catalogService->getProducts(
            $currentFilters, $sort, $filterRequest->input('search')
        );

        $sortingList = [
            'rating' => 'по популярности',
            'newness' => 'новинки',
            'price-up' => 'по возрастанию цены',
            'price-down' => 'по убыванию цены',
        ];

        $category = end($currentFilters[Category::class])->getFilterModel();

        return view('shop.catalog', [
            'products' => $products,
            'category' => $category,
            'currentFilters' => $currentFilters,
            'filters' => Filter::all(),
            'sort' => $sort,
            'sortingList' => $sortingList,
        ]);
    }
}
