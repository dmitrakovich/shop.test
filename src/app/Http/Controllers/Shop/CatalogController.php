<?php

namespace App\Http\Controllers\Shop;

use App\Models\Filter;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\UrlHelper;
use Illuminate\Support\Str;
use App\Services\ProductService;
use App\Http\Requests\FilterRequest;

class CatalogController extends BaseController
{
    /**
     * Количество товаров на странице
     */
    protected const PAGE_SIZE = 12;


    public function ajaxNextPage()
    {
        // в будущем создать отдельный view для подгрузки только моделей,
        // а не всей страницы целиком

        // cursor
        // @see https://laravel.demiart.ru/offset-vs-cursor-pagination/
    }

    public function show(ProductService $productService, FilterRequest $filterRequest)
    {
        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        UrlHelper::setCurrentFilters($currentFilters);

        // dump($currentFilters);

        $products = $productService->applyFilters($currentFilters)
            ->with([
                'category:id,title,path',
                'brand:id,name',
                'sizes:id,name',
                'media',
                'styles:id,name',
            ])
            ->search($filterRequest->input('search'))
            ->sorting($sort)
            ->paginate(self::PAGE_SIZE);

        abort_if(empty($products), 404);

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
            'category',
            'categoryTitle',
            'currentFilters',
            'filters',
            'sort',
            'sortingList'
        );

        return view('shop.catalog', $data);
    }
}
