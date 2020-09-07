<?php

namespace App\Http\Controllers\Shop;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{

    public function show($slug, $params)
    {
        // $product = Product::find($id);
        /*$product = Product::findOrFail($id);
        dd($product);
        dd($request);*/


        return 'slug = '.$slug.'<br>params = '.print_r($params, true);
    }
}
