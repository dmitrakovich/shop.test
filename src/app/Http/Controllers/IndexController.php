<?php

namespace App\Http\Controllers;

use App\Services\GoogleTagManagerService;
use App\Services\SliderService;
use Illuminate\Contracts\View\View;

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(
        SliderService $sliderService,
        GoogleTagManagerService $gtmService
    ): View {
        $gtmService->setViewForIndex();

        return view('index', [
            'instagramPosts' => [],
            'instagramTitle' => '',
            'linksBlocks' => collect(),
            'simpleSliders' => $sliderService->getFormattedSimple(),
        ]);
    }
}
