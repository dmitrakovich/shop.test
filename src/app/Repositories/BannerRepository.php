<?php

namespace App\Repositories;

use App\Enums\Ads\BannerPosition;
use App\Models\Ads\Banner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BannerRepository
{
    private const string MAIN_BANNERS_CACHE_KEY = 'banners.index_main';

    public function __construct(private readonly Banner $model) {}

    /**
     * @return Collection<int, Banner>
     */
    public function getMainBanners(): Collection
    {
        return Cache::rememberForever(self::MAIN_BANNERS_CACHE_KEY, function () {
            return $this->model->newQuery()
                ->active()
                ->where('position', BannerPosition::INDEX_MAIN)
                ->orderByPriority()
                ->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::MAIN_BANNERS_CACHE_KEY);
    }
}
