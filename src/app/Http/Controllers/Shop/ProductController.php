<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use App\Services\GoogleTagManagerService;
use App\Services\SliderService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ProductController extends BaseController
{
    /**
     * ProductController constructor.
     */
    public function __construct(
        Request $request,
        private ProductService $productService,
        private SliderService $sliderService,
        private GoogleTagManagerService $gtmService,
    ) {
        parent::__construct($request);
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): View
    {
        $product = Product::with(['tags', 'category'])->findOrFail($id);

        $this->gtmService->setViewForProduct($product);
        $this->productService->addToRecent($product->id);

        return view('shop.product-page', [
            'product' => $product,
            'dataLayer' => $this->gtmService->prepareProduct($product),
            'similarProducts' => $this->sliderService->getSimilarProducts($product->id),
            'recentProductsSlider' =>$this->sliderService->getRecentProducts($this->productService),
        ]);
    }

    /**
     * Quick view
     */
    public function quickView(Product $product): View
    {
        $dataLayer = $this->gtmService->prepareProduct($product);

        return view('shop.product', compact('product', 'dataLayer'));
    }
}
