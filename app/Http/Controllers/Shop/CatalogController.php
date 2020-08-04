<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends BaseController
{
    public function index()
    {
        $products = Product::paginate(15);
        // dd($products);
        return view('shop.catalog', compact('products'));
    }
}
