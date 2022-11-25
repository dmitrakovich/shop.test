<?php

namespace App\Http\Controllers\Shop;

use App\Helpers\UrlHelper;
use App\Http\Requests\FilterRequest;
use App\Models\Category;
use App\Models\ProductAttributes\Price;
use App\Services\CatalogService;
use App\Services\FilterService;
use App\Services\GoogleTagManagerService;
use App\Services\Seo\CatalogSeoService;
use App\Services\SliderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogController extends BaseController
{
    /**
     * CatalogController constructor.
     */
    public function __construct(
        Request $request,
        private GoogleTagManagerService $gtmService,
        private CatalogService $catalogService,
        private CatalogSeoService $seoService,
    ) {
        parent::__construct($request);
    }

    /**
     * Render products for next page
     */
    public function ajaxNextPage(): array
    {
        $this->request->validate(['cursor' => 'required']);

        $products = $this->catalogService->getNextProducts();

        $renderedProducts = [];
        foreach ($products as $product) {
            $renderedProducts[] = view('shop.catalog-product', compact('product'))->render();
        }

        return [
            'rendered_products' => $renderedProducts,
            'cursor' => optional($products->nextCursor())->encode(),
            'has_more' => $products->hasMorePages(),
            'data_layers' => $this->gtmService->getForCatalogArrays(
                $products,
                $this->request->input('category'),
                $this->request->input('search')
            ),
        ];
    }

    /**
     * Show catalog page
     */
    public function show(FilterRequest $filterRequest)
    {
        $sort = $filterRequest->getSorting();
        $currentFilters = $filterRequest->getFilters();
        $currentCity = $filterRequest->getCity();
        $searchQuery = $filterRequest->input('search');
        UrlHelper::setCurrentFilters($currentFilters);
        UrlHelper::setCurrentCity($currentCity);

        $products = $this->catalogService->getProducts($currentFilters, $sort, $searchQuery);

        $sortingList = [
            'rating' => 'по популярности',
            'newness' => 'новинки',
            'price-up' => 'по возрастанию цены',
            'price-down' => 'по убыванию цены',
        ];

        $category = end($currentFilters[Category::class])->getFilterModel();
        $badges = $this->catalogService->getFilterBadges($currentFilters, $searchQuery);

        $this->gtmService->setForCatalog($products, $category, $searchQuery);

        $data = [
            'products' => $products,
            'category' => $category,
            'currentFilters' => $currentFilters,
            'badges' => $badges,
            'filters' => app(FilterService::class)->getAll(),
            'sort' => $sort,
            'sortingList' => $sortingList,
            'searchQuery' => $searchQuery,
        ];

        if (!$products->isNotEmpty()) {
            $sliderService = new SliderService;
            $data['simpleSliders'] = $sliderService->getSimple();
        }
        $this->seoService->setCurrentFilters($currentFilters)->setCurrentCity($currentCity)->setProducts($products)->generate();

        return view('shop.catalog', $data);
    }

    /**
     * Generate url with price filter slugs & redirect to it
     */
    public function priceFilter(FilterRequest $filterRequest, FilterService $filterService): RedirectResponse
    {
        UrlHelper::setCurrentFilters($filterRequest->getFilters());
        $add = $filterService->makePriceFilters($filterRequest->input());

        return redirect(UrlHelper::generate($add, [['model' => Price::class]]));
    }
}
