<?php

namespace App\Http\Controllers\Shop;

use App\Product;
use App\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Cache;

class CatalogController extends BaseController
{
    /**
     * Количество товаров на странице
     */
    protected const PAGE_SIZE = 30;
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

    public function show($slug, $params = null)
    {
        dd($slug, $params);

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
   
        $categoriesTree =  $this->categoryRepository->getTree();

        // $products = Product::paginate(15);
        $products = $this->productRepository->getAllWithPaginate(self::PAGE_SIZE);
        if (empty($products)) {
            abort(404);
        }
        // dd($products->first()->category);
        return view('shop.catalog', compact('products', 'categoriesTree'));
    }
}
