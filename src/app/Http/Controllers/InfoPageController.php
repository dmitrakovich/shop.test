<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use App\Services\GoogleTagManagerService;
use App\Services\StockService;
use Illuminate\Contracts\View\View;
use SeoFacade;

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

        SeoFacade::setTitle($currentInfoPage['name']);

        return view('static.template', compact('currentInfoPage'));
    }

    /**
     * Display terms info page.
     */
    public function terms(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();
        SeoFacade::setTitle('Публичная оферта');

        return view('static.terms');
    }

    /**
     * Display private policy info page.
     */
    public function policy(GoogleTagManagerService $gtmService): View
    {
        $gtmService->setViewForOther();
        SeoFacade::setTitle('Политика конфиденциальности');

        return view('static.policy');
    }

    /**
     * Display shops info page.
     */
    public function shops(
        StockService $stockService
    ): View {
        return view('static.shops', [
            'shops' => $stockService->getShops(),
        ]);
    }
}
