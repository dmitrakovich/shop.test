<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use App\Models\Category;
use App\Models\Filter;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Models\Url;
use Illuminate\Support\Facades\Cache;

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
     * ProductRepository
     *
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * CategoryRepository
     *
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct()
    {
        parent::__construct();

        $this->productRepository = app(ProductRepository::class);
        $this->categoryRepository = app(CategoryRepository::class);
    }
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
            return Url::whereIn('slug', $slugs)
                ->get(['slug', 'model_type', 'model_id'])
                ->groupBy('model_type')
                ->toArray();
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

        // $currentCategory = Category::find($slug->model_id);
        $currentCategory = Category::first();
        // dd($slug, $currentCategory);
        $categoriesTree =  $this->categoryRepository->getTree();

        $products = $this->applyFilters($currentFilters)
            ->with([
                'category',
                'brand',
                // 'images',
                'sizes',
                'color',
                'fabrics',
                'media',
            ])
            ->where('publish', true)
            ->sorting($sort)
            ->paginate(self::PAGE_SIZE);

        // $products = $this->productRepository->getAllWithPaginate(self::PAGE_SIZE);
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
            'currentCategory',
            'categoriesTree',
            'filters',
            'sort',
            'sortingList'
        );

        return view('shop.catalog', $data);
    }
}
