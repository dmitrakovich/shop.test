<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use App\Services\GoogleTagManagerService;
use Illuminate\Contracts\View\View;

class InfoPageController extends Controller
{
    /**
     * Display the specified info page.
     */
    public function index(GoogleTagManagerService $gtmService, ?string $slug = null): View
    {
        $currentInfoPage = InfoPage::when($slug, fn ($query) => $query->where('slug', $slug))
            ->firstOrFail(['slug', 'name', 'html'])
            ->toArray();

        $gtmService->setViewForOther();

        return view('static.template', compact('currentInfoPage'));
    }

    /**
     * Display terms info page.
     */
    public function terms(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();

        return view('static.terms');
    }

    /**
     * Display private policy info page.
     */
    public function policy(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();

        return view('static.policy');
    }
}
