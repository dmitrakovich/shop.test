<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockTypeEnum;
use App\Events\Analytics\ProductView;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\FeedbackService;
use App\Services\ProductService;
use App\Services\Seo\ProductSeoService;
use App\Services\SliderService;

class ProductController extends Controller
{
    public function __construct(
        // private readonly ProductService $productService,
        private readonly SliderService $sliderService,
        // private readonly ProductSeoService $seoService,
        private readonly FeedbackService $feedbackService,
    ) {}

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'tags',
            'category',
            'countryOfOrigin',
            'availableSizes' => fn ($q) => $q->whereHas('stock', fn ($q) => $q->where('type', StockTypeEnum::SHOP))
                ->with(['stock' => fn ($q) => $q->orderBy('site_sorting', 'asc')->with('city')]),
        ]);
        // $this->productService->addToRecent($product->id);

        // $this->seoService->setProduct($product)->generate(); // !!!

        event(new ProductView($product));

        return [
            'product' => $product,
            'feedbacks' => $this->feedbackService->getForProduct($product->id),
            'similarProducts' => $this->sliderService->getSimilarProducts($product->id),
            'productGroup' => $this->sliderService->getProductGroup($product->product_group_id),
            // 'recentProductsSlider' => $this->sliderService->getRecentProducts($this->productService),
        ];
    }
}
