<?php

namespace App\Http\Controllers\Shop;

use App\Models\Product;
use App\Services\GoogleTagManagerService;
use App\Services\Seo\TitleGenerotorService;
use App\Services\ProductService;
use App\Services\SliderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

use SeoFacade;

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
        private TitleGenerotorService $seoService,
    ) {
        parent::__construct($request);
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): View
    {
        $product = Product::with(['tags', 'category'])->withTrashed()->findOrFail($id);
        $this->gtmService->setViewForProduct($product);
        $this->productService->addToRecent($product->id);

        SeoFacade::setTitle($this->seoService->getProductTitle($product))
            ->setDescription($this->seoService->getProductDescription($product))
            ->setImage($product->getFirstMedia()->getUrl('catalog'));

        return view('shop.product-page', [
            'product' => $product,
            'dataLayer' => $this->gtmService->prepareProduct($product),
            'similarProducts' => $this->sliderService->getSimilarProducts($product->id),
            'recentProductsSlider' => $this->sliderService->getRecentProducts($this->productService),
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
