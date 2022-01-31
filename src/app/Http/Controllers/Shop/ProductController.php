<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use App\Models\Url;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * Display the specified product.
     *
     * @param Url $url
     * @param array $params
     * @return View
     */
    public function show(Url $url, array $params): View
    {
        $product = Product::findOrFail($url->model_id);

        return view('shop.product-page', compact('product'));
    }

    /**
     * Quick view
     *
     * @param Product $product
     * @return View
     */
    public function quickView(Product $product): View
    {
        return view('shop.product', compact('product'));
    }
}
