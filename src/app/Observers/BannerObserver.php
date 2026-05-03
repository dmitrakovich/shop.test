<?php

namespace App\Observers;

use App\Models\Ads\Banner;
use App\Repositories\BannerRepository;

class BannerObserver
{
    public function saved(Banner $banner): void
    {
        app(BannerRepository::class)->clearCache();
    }

    public function deleted(Banner $banner): void
    {
        app(BannerRepository::class)->clearCache();
    }

    public function restored(Banner $banner): void
    {
        app(BannerRepository::class)->clearCache();
    }
}
