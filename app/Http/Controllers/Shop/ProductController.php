<?php

namespace App\Http\Controllers\Shop;

use App\Product;
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


        return view('shop.product', compact('product'));
    }
}
