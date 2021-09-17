<?php

namespace App\Http\Controllers\Shop;

use App\Models\Filter;
use App\Models\Category;
use App\Helpers\UrlHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Cache;
use Laravie\SerializesQuery\Eloquent;
use Illuminate\Support\Facades\Session;

class CatalogController extends BaseController
{
    /**
     * Количество товаров на странице
     */
    protected const PAGE_SIZE = 12;

    /**
     * Render products for next page
     *
     * @return array
     */
    public function ajaxNextPage(Request $request)
    {
        $request->validate(['cursor' => 'required']);

        $productsQuery = Cache::get($this->getQueryCacheKey());
        abort_if(empty($productsQuery), 419, 'Query cache not found');

        $productsQuery = Eloquent::unserialize($productsQuery);
        $products = $productsQuery->cursorPaginate(self::PAGE_SIZE);

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

    public function show(ProductService $productService, FilterRequest $filterRequest)
    {
        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        UrlHelper::setCurrentFilters($currentFilters);
        // dump($currentFilters);

        $productsQuery = $productService->getForCatalog(
            $currentFilters, $sort, $filterRequest->input('search')
        );

        $productsTotal = $productsQuery->count();
        $products = $productsQuery->cursorPaginate(self::PAGE_SIZE);

        // save query in cache (100 minutes)
        Cache::put($this->getQueryCacheKey(), Eloquent::serialize($productsQuery), 6000);

        $filters = Filter::all();
        $sortingList = [
            'rating' => 'по популярности',
            'newness' => 'новинки',
            'price-up' => 'по возрастанию цены',
            'price-down' => 'по убыванию цены',
        ];
        // dd($filters);


         // временное решение
        if (isset($currentFilters['App\Models\Category'])) {
            $category = Category::find(end($currentFilters['App\Models\Category'])['model_id']);
            $categoryTitle = $category->title;
        } else {
            $category = Category::first();
            $categoryTitle = 'женскую обувь';
        }
        $categoryTitle = Str::lower($categoryTitle);

        $data = compact(
            'products',
            'productsTotal',
            'category',
            'categoryTitle',
            'currentFilters',
            'filters',
            'sort',
            'sortingList'
        );

        return view('shop.catalog', $data);
    }

    /**
     * Generate key for set/get query cahce
     *
     * @return string
     */
    protected function getQueryCacheKey(): string
    {
        return 'catalog-query-' . Session::getId();
    }
}
