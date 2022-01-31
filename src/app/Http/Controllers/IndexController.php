<?php

namespace App\Http\Controllers;

use App\Models\Ads\IndexLink;
use App\Services\InstagramService;
use App\Services\SliderService;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param InstagramService $instagramService
     * @param SliderService $sliderService
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(InstagramService $instagramService, SliderService $sliderService)
    {
        return view('index', [
            'imidjSlider' => $sliderService->getImidj(),
            'instagramPosts' => array_slice($instagramService->getCachedPosts(), 0, 6),
            'instagramTitle' => $instagramService->getTitle(),
            'linksBlocks' => IndexLink::get(['id', 'title', 'links']),
            'simpleSliders' => $sliderService->getSimple()
        ]);
    }
}
