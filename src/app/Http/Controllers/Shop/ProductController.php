<?php

namespace App\Http\Controllers\Shop;

use App\Models\Url;
use App\Models\Product;
use App\Services\GoogleTagManagerService;
use App\Services\SliderService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ProductController extends BaseController
{
    /**
     * Display the specified product.
     */
    public function show(Url $url, array $params, GoogleTagManagerService $gtmService): View
    {
        $product = Product::with(['tags', 'category'])->findOrFail($url->model_id);

        $gtmService->setViewForProduct($product);
        $dataLayer = $gtmService->prepareProduct($product);
        $productService = new ProductService;
        $productService->addToRecent($product->id);
        $sliderService   = new SliderService; 
        $similarProducts = $sliderService->getSimilarProducts($product->id);
        $recentProductsSlider = $sliderService->getRecentProducts($productService);

        return view('shop.product-page', compact('product', 'dataLayer', 'similarProducts', 'recentProductsSlider'));
    }

    /**
     * Quick view
     */
    public function quickView(Product $product, GoogleTagManagerService $gtmService): View
    {
        $dataLayer = $gtmService->prepareProduct($product);

        return view('shop.product', compact('product', 'dataLayer'));
    }
}
