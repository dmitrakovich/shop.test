<?php

namespace App\Http\Controllers\Shop;

use App\Models\Filter;
use App\Models\Category;
use App\Helpers\UrlHelper;
use Illuminate\Support\Str;
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

        // временное решение
        if (isset($currentFilters['App\Models\Category'])) {
            $category = Category::find(end($currentFilters['App\Models\Category'])['model_id']);
            $categoryTitle = $category->title;
        } else {
            $category = Category::first();
            $categoryTitle = 'женскую обувь';
        }

        return view('shop.catalog', [
            'products' => $products,
            'category' => $category,
            'categoryTitle' => Str::lower($categoryTitle),
            'currentFilters' => $currentFilters,
            'filters' => Filter::all(),
            'sort' => $sort,
            'sortingList' => $sortingList,
        ]);
    }
}
