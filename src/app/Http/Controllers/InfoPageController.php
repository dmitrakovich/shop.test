<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use Illuminate\Contracts\View\View;
use App\Services\GoogleTagManagerService;

class InfoPageController extends Controller
{
    /**
     * Display the specified info page.
     *
     * @param GoogleTagManagerService $gtmService
     * @param string|null $slug
     * @return View
     */
    public function index(GoogleTagManagerService $gtmService, ?string $slug = null): View
    {
        $currentInfoPage = InfoPage::when($slug, function ($query) use ($slug) {
            return $query->where('slug', $slug);
        })
        ->firstOrFail(['slug', 'name', 'html'])
        ->toArray();

        $gtmService->setViewForOther();

        return view('static.template', compact('currentInfoPage'));
    }

    /**
     * Display terms info page.
     *
     * @param GoogleTagManagerService $gtmService
     * @return View
     */
    public function terms(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();

        return view('static.terms');
    }

    /**
     * Display private policy info page.
     *
     * @param GoogleTagManagerService $gtmService
     * @return View
     */
    public function policy(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();

        return view('static.policy');
    }
}
