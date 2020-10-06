<?php

namespace App\Http\Controllers\Shop;

use App\Product;
use App\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Url;
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

    public function __construct()
    {
        parent::__construct();

        $this->productRepository = app(ProductRepository::class);
        $this->categoryRepository = app(CategoryRepository::class);
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
        $filters = [];

        unset($slugs[0]); // catalog

        if (!empty($slugs)) {


            $filters = Url::whereIn('slug', $slugs)
                ->get()
                ->groupBy('model_type');
            dd($filters);

        } else {
            return [];
        }
        
        /*foreach ($slugs as $slug) {

            $filter = Cache::tags(['slugs'])
                ->rememberForever("slugs.$slug", function () use ($slug) {
                    return Url::where('slug', $slug)
                        ->with('attribute:id,value')
                        ->first(['slug', 'model_type', 'model_id'])
                        ->toArray();
                });

            dump($filter);

            $filters[$filter['model_type']][$filter['model_id']] = [
                'slug' => $filter,
                'name' => Filter::getNamePrefix($filter['model_type']) . $filter['attribute']['value'],
            ];
        }

        return $filters;*/
    }

    public function show($request)
    {
        $filters = $this->getFilters($request);


        dd($filters);

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
   
        
        $currentCategory = Category::find($slug->model_id);
        // dd($slug, $currentCategory);
        $categoriesTree =  $this->categoryRepository->getTree();


        // Product::where('category_id', 0)->delete();

        $products = Product::with([
            'category',
            'brand',
            'images',
            'sizes',
            'color',
            'fabrics',
        ])->paginate(self::PAGE_SIZE);

        // $products = $this->productRepository->getAllWithPaginate(self::PAGE_SIZE);
        abort_if(empty($products), 404);
        // dd($products->first());
        return view('shop.catalog', compact('products', 'currentCategory', 'categoriesTree'));
    }
}
