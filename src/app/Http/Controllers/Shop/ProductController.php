<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{

    public function show($slug, $params)
    {
        $product = Product::findOrFail($slug->model_id);

        return view('shop.product-page', compact('product'));
    }
    /**
     * Быстрый просмотр
     *
     * @param integer $id
     * @return void
     */
    public function quickView(int $id)
    {
        $product = Product::findOrFail($id);

        return view('shop.product', compact('product'));
    }
}
