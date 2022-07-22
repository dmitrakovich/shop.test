<?php

namespace App\Http\Controllers;

use App\Models\Ads\IndexLink;
use App\Services\GoogleTagManagerService;
use App\Services\InstagramService;
use App\Services\SliderService;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(
        InstagramService $instagramService,
        SliderService $sliderService,
        GoogleTagManagerService $gtmService
    ) {
        $gtmService->setViewForIndex();

        return view('index', [
            'imidjSlider' => $sliderService->getImidj(),
            //'instagramPosts' => array_slice($instagramService->getCachedPosts(), 0, 6),
            'instagramPosts' => array(),
            'instagramTitle' => $instagramService->getTitle(),
            'linksBlocks' => IndexLink::get(['id', 'title', 'links']),
            'simpleSliders' => $sliderService->getSimple()
        ]);
    }
}
