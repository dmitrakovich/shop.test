<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Info\PageResource;
use App\Http\Resources\Info\ShopResource;
use App\Http\Resources\Product\CatalogProductResource;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Ads\IndexLink;
use App\Models\InfoPage;
use App\Services\GoogleTagManagerService;
use App\Services\InstagramService;
use App\Services\SliderService;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InfoPageController extends Controller
{
    /**
     * Show the application main page.
     */
    public function main(
        InstagramService $instagramService,
        SliderService $sliderService,
        GoogleTagManagerService $gtmService
    ): array {
        // $gtmService->setViewForIndex();

        return [
            'imidjSlider' => CatalogProductResource::collection($sliderService->getImidjProducts()),
            'instagramPosts' => [], // array_slice($instagramService->getCachedPosts(), 0, 6),
            'instagramTitle' => $instagramService->getTitle(),
            'linksBlocks' => IndexLink::query()->get(['id', 'title', 'links']),
            'simpleSliders' => array_map(
                fn (Collection $slider) => CatalogProductResource::collection($slider),
                $sliderService->getSimpleProducts()
            ),
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
