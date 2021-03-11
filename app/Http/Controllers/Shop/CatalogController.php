<?php

namespace App\Http\Controllers\Shop;

use App\Helpers\UrlHelper;
use App\Models\Category;
use App\Models\Filter;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\Request;

class CatalogController extends BaseController
{
    /**
     * Количество товаров на странице
     */
    protected const PAGE_SIZE = 12;
    /**
     * Сортировка по умлочанию
     */
    const DEFAULT_SORT = 'newness';
    /**
     * Применить фильтры к выборке
     *
     * @param array $filters
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected function applyFilters(array $filters)
    {
        $query = (new Product())->newQuery();

        foreach ($filters as $filterName => $filterValues) {
            if (class_exists($filterName) && method_exists($filterName, 'applyFilter')) {
                $query = $filterName::applyFilter($query, array_column($filterValues, 'model_id'));
            } else {
                continue;
            }
        }
        return $query;
    }

    public function ajaxNextPage()
    {
        // в будущем создать отдельный view для подгрузки только моделей,
        // а не всей страницы целиком
    }

    /**
     * Получить фильтра
     *
     * @return array
     */
    public function getFilters($request)
    {
        $slugs = $request->path() ? explode('/', $request->path()) : [];
        unset($slugs[0]); // catalog

        if (!empty($slugs)) {
            $filters = [];
            $filtersTemp = Url::whereIn('slug', $slugs)
                ->with('filters') // :id,name !!!
                ->get(['slug', 'model_type', 'model_id']);

            foreach ($filtersTemp as $value) {
                $filters[$value->model_type][$value->slug] = $value->toArray();
            }
            // говнокод на скорую руку для сортировки категорий в правильном порядке
            if (isset($filters['App\Models\Category'])) {
                $categoriesFilters = $filters['App\Models\Category'];
                $filters['App\Models\Category'] = [];
                foreach ($slugs as $slug) {
                    if (isset($categoriesFilters[$slug])) {
                        $filters['App\Models\Category'][$slug] = $categoriesFilters[$slug];
                    }
                }
            }
            return $filters;
        } else {
            return [];
        }
    }

    protected function getSorting($request)
    {
        $sorting = $request->input('sort') ?? session()->get('sorting', self::DEFAULT_SORT);
        if (session()->get('sorting') <> $sorting) {
            session()->put('sorting', $sorting);
            session()->save();
        }
        return $sorting;
    }

    public function show($request)
    {
        $sort = $this->getSorting($request);
        $currentFilters = $this->getFilters($request);
        UrlHelper::setCurrentFilters($currentFilters);

        // dump($currentFilters);

        $products = $this->applyFilters($currentFilters)
            ->with([
                'category',
                'brand',
                // 'images',
                'sizes',
                // 'color',
                'fabrics',
                'media',
            ])
            ->where('publish', true)
            ->search($request->input('search'))
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

        $data = compact(
            'products',
            'currentFilters',
            'filters',
            'sort',
            'sortingList'
        );

        return view('shop.catalog', $data);
    }
}
