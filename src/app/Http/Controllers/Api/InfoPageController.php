<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Info\PageResource;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\InfoPage;
use App\Services\GoogleTagManagerService;

class InfoPageController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(InfoPage $page, GoogleTagManagerService $gtmService): PageResource
    {
        // $gtmService->setViewForOther();
        // SeoFacade::setTitle($currentInfoPage['name']);

        return new PageResource($page);
    }
}
