<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{

    public function show($slug, $params)
    {
        // dump($slug);
        $product = Product::find($slug->model_id);
        /*$product = Product::findOrFail($id);
        dd($product);
        dd($request);*/

        // $product = 'slug = '.$slug.'<br>params = '.print_r($params, true);


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
