<?php

namespace App\Http\Controllers\Shop;

use App\Models\Url;
use App\Models\Product;
use App\Services\GoogleTagManagerService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

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

        (new GoogleTagManagerService)->setViewForProduct($product);

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
