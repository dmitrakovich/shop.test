<?php

namespace App\Http\Controllers\Shop;

use App\Enums\StockTypeEnum;
use App\Events\Analytics\ProductView;
use App\Models\Product;
use App\Services\FeedbackService;
use App\Services\ProductService;
use App\Services\Seo\ProductSeoService;
use App\Services\SliderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * ProductController constructor.
     */
    public function __construct(
        Request $request,
        private ProductService $productService,
        private SliderService $sliderService,
        private ProductSeoService $seoService,
        private FeedbackService $feedbackService,
    ) {
        parent::__construct($request);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load([
            'tags',
            'category',
            'countryOfOrigin',
            'availableSizes' => fn ($q) => $q->whereHas('stock', fn ($q) => $q->where('type', StockTypeEnum::SHOP))
                ->with(['stock' => fn ($q) => $q->orderBy('site_sorting', 'asc')->with('city')]),
        ]);
        $this->productService->addToRecent($product->id);
        $this->setProductUrlToFeedback();

        $this->seoService->setProduct($product)->generate();

        event(new ProductView($product));

        return view('shop.product-page', [
            'product' => $product,
            'feedbacks' => $this->feedbackService->getForProduct($product->id),
            'similarProducts' => $this->sliderService->getSimilarProducts($product->id),
            'productGroup' => $this->sliderService->getProductGroup($product->product_group_id),
            'recentProductsSlider' => $this->sliderService->getRecentProducts($this->productService),
        ]);
    }

    /**
     * Save url to product page with review, for auth redirect
     */
    protected function setProductUrlToFeedback(): void
    {
        $this->request->session()->put('url.intended', $this->request->fullUrl() . '#review');
    }

    /**
     * Quick view
     */
    public function quickView(Product $product): View
    {
        $product->loadMissing([
            'tags',
            'category',
            'countryOfOrigin',
            'availableSizes' => fn ($q) => $q->whereHas('stock', fn ($q) => $q->where('type', StockTypeEnum::SHOP))
                ->with(['stock' => fn ($q) => $q->orderBy('site_sorting', 'asc')->with('city')]),
        ]);
        event(new ProductView($product, true));

        return view('shop.product', [
            'quickView' => true,
            'product' => $product,
            'feedbacks' => $this->feedbackService->getForProduct($product->id),
            'productGroup' => $this->sliderService->getProductGroup($product->product_group_id),
        ]);
    }
}
