<?php

namespace App\Http\Controllers;

use App\Models\Ads\IndexLink;
use App\Services\GoogleTagManagerService;
use App\Services\InstagramService;
use App\Services\SliderService;
use Illuminate\Contracts\View\View;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(
        InstagramService $instagramService,
        SliderService $sliderService,
        GoogleTagManagerService $gtmService
    ): View {
        $gtmService->setViewForIndex();

        return view('index', [
            'imidjSlider' => $sliderService->getImidj(),
            'instagramPosts' => [], // array_slice($instagramService->getCachedPosts(), 0, 6),
            'instagramTitle' => $instagramService->getTitle(),
            'linksBlocks' => IndexLink::query()->get(['id', 'title', 'links']),
            'simpleSliders' => $sliderService->getSimple(),
        ]);
    }
}
