<?php

namespace App\Repositories;

use App\Enums\Ads\BannerPosition;
use App\Models\Ads\Banner;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class BannerRepository
{
    private const string CACHE_VERSION_KEY = 'banners.cache_version';

    private const int FALLBACK_TTL_SECONDS = 600;

    private const int MIN_TTL_SECONDS = 30;

    public function __construct(
        private readonly Banner $model,
        private readonly CacheRepository $cache,
    ) {}

    /**
     * @return Collection<int, Banner>
     */
    public function getMainBanners(): Collection
    {
        return $this->getBannersByPosition(BannerPosition::INDEX_MAIN);
    }

    /**
     * Active banners for the catalog listing (desktop + mobile on each record).
     *
     * @return Collection<int, Banner>
     */
    public function getCatalogBanners(): Collection
    {
        return $this->getBannersByPosition(BannerPosition::CATALOG_MAIN);
    }

    /**
     * Active banners for the reviews / feedback listing.
     *
     * @return Collection<int, Banner>
     */
    public function getFeedbackBanners(): Collection
    {
        return $this->getBannersByPosition(BannerPosition::FEEDBACK_MAIN);
    }

    /**
     * @return Collection<int, Banner>
     */
    public function getBannersByPosition(BannerPosition $position): Collection
    {
        $version = (int)$this->cache->get(self::CACHE_VERSION_KEY, 1);
        $cacheKey = "banners.v{$version}.position.{$position->value}";

        return $this->cache->remember($cacheKey, $this->ttlForPosition($position), function () use ($position) {
            return $this->model->newQuery()
                ->active()
                ->where('position', $position)
                ->orderByPriority()
                ->with('media')
                ->get();
        });
    }

    public function clearCache(): void
    {
        if (!$this->cache->increment(self::CACHE_VERSION_KEY)) {
            $this->cache->forever(self::CACHE_VERSION_KEY, 2);
        }
    }

    private function ttlForPosition(BannerPosition $position): int
    {
        $now = now();

        $nextEnd = $this->model->newQuery()
            ->where('position', $position)
            ->active()
            ->whereNotNull('end_datetime')
            ->min('end_datetime');

        $nextStart = $this->model->newQuery()
            ->where('position', $position)
            ->where('active', true)
            ->whereNotNull('start_datetime')
            ->where('start_datetime', '>', $now)
            ->min('start_datetime');

        $nextChangeAt = collect([$nextEnd, $nextStart])
            ->filter()
            ->map(fn (string $value) => Carbon::parse($value))
            ->sortBy(fn (Carbon $date) => $date->getTimestamp())
            ->first();

        if (!$nextChangeAt instanceof Carbon) {
            return self::FALLBACK_TTL_SECONDS;
        }

        $seconds = $now->diffInSeconds($nextChangeAt, false);

        return max(self::MIN_TTL_SECONDS, $seconds);
    }
}
