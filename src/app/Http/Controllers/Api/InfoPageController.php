<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Info\PageResource;
use App\Http\Resources\Info\ShopResource;
use App\Http\Resources\Product\CatalogProductResource;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\InfoPage;
use App\Services\GoogleTagManagerService;
use App\Services\SliderService;
use App\Services\StockService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InfoPageController extends Controller
{
    /**
     * Show the application main page.
     */
    public function main(
        SliderService $sliderService,
    ): array {
        return [
            'hitSlider' => CatalogProductResource::collection($sliderService->getImidjProducts()),
            'trendSlider' => CatalogProductResource::collection($sliderService->getImidjProducts()),
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(InfoPage $page, GoogleTagManagerService $gtmService): PageResource
    {
        // $gtmService->setViewForOther();
        // SeoFacade::setTitle($currentInfoPage['name']);

        return new PageResource($page);
    }

    /**
     * Display shops resource.
     */
    public function shops(StockService $stockService): AnonymousResourceCollection
    {
        return ShopResource::collection($stockService->getShops());
    }
}
