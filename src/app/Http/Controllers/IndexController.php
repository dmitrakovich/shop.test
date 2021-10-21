<?php

namespace App\Http\Controllers;

use App\Models\Ads\IndexLink;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Ads\ProductCarousel;
use App\Services\InstagramService;

class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param InstagramService $instagramService
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(InstagramService $instagramService)
    {
        return view('index', [
            'instagramPosts' => array_slice($instagramService->getCachedPosts(), 0, 6),
            'instagramTitle' => $instagramService->getTitle(),
            'linksBlocks' => IndexLink::get(['id', 'title', 'links']),
            'productCarousels' => $this->getProductCarousels()
        ]);
    }

    /**
     * Get product carousels
     *
     * @return array
     */
    protected function getProductCarousels(): array
    {
        $productCarousels = [];
        $carousels = ProductCarousel::ordered()
            ->get(['title', 'category_id', 'only_sale', 'only_new', 'count']);

        foreach ($carousels as $key => $carousel) {
            $categories = Category::getChildrenCategoriesIdsList($carousel->category_id);
            $products = Product::whereIn('category_id', $categories)
                ->when($carousel->only_sale, function ($query) {
                    $query->onlyWithSale();
                })
                ->when($carousel->only_new, function ($query) {
                    $query->onlyNew();
                })
                ->sorting('rating')
                ->limit($carousel->count)
                ->with(['media', 'category', 'brand'])
                ->get();

            if (count($products)) {
                $productCarousels[$key] = [
                    'title' => $carousel->title,
                    'products' => $products
                ];
            }
        }

        return $productCarousels;
    }
}
