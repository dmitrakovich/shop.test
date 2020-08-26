<?php

namespace App\Http\Controllers\Shop;

use App\Product;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;

class CatalogController extends BaseController
{
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
    }

    public function index()
    {
        // $products = Product::paginate(15);
        $products = $this->productRepository->getAllWithPaginate(15);
        if (empty($products)) {
            abort(404);
        }
        // dd($products);
        return view('shop.catalog', compact('products'));
    }
}
