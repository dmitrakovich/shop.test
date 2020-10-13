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

    public function show($request)
    {
        $currentFilters = $this->getFilters($request);


        // dump($currentFilters);

        /*if ($path) {
            if ($this->categoryRepository->hasPath($path)) {
                // страница каталога
            } else {
                $productSlug = basename($path);
                $path = substr($path, 0, -(++strlen($productSlug)));
                if ($this->categoryRepository->hasPath($path)) {
                    // страница продукта
                } else { // такая категория не найдена
                    abort(404);
                }
            }
        }*/
        

        // есть ли такая каегория
        // есть ли такая каегория -1
            // если есть, то в конце товар
            // если нет, то 404
   
        
        // $currentCategory = Category::find($slug->model_id);
        $currentCategory = Category::first();
        // dd($slug, $currentCategory);
        $categoriesTree =  $this->categoryRepository->getTree();


        // Product::where('category_id', 0)->delete();

        $products = $this->applyFilters($currentFilters)
            ->with([
                'category',
                'brand',
                'images',
                'sizes',
                'color',
                'fabrics',
            ])
            ->where('product_publish', true)
            // ->orderBy('created_at', 'desc')
            ->paginate(self::PAGE_SIZE);

        // $products = $this->productRepository->getAllWithPaginate(self::PAGE_SIZE);
        abort_if(empty($products), 404);

        $filters = Filter::all();
        // dd($filters);

        return view('shop.catalog', compact('products', 'currentCategory', 'categoriesTree', 'filters'));
    }
}
