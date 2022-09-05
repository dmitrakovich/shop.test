<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        SoftDeletes;

    protected $casts = [
      'spoiler'      => 'json',
    ];

    public static function getIndexMain()
    {
        $indexMainBanner = self::active()
            ->bannerFields()
            ->where('position', 'index_main')
            ->with('media')
            ->orderByPriority()
            ->first();

        $indexTopBanners = self::active()
            ->bannerFields()
            ->where('position', 'index_top')
            ->with('media')
            ->orderByPriority()
            ->limit(3)
            ->get();

        return view('banners.index-main', compact('indexMainBanner', 'indexTopBanners'));
    }

    public static function getIndexBottom()
    {
        $indexBottomBanners = self::active()
            ->bannerFields()
            ->where('position', 'index_bottom')
            ->with('media')
            ->orderByDesc('priority')
            ->get();

        return view('banners.index-bottom', compact('indexBottomBanners'));
    }

    public static function getCatalogTop()
    {
        $catalogBanner = self::active()
            ->bannerFields()
            ->where('position', 'catalog_top')
            ->with('media')
            ->orderByPriority()
            ->first();

        return view('banners.catalog-top', compact('catalogBanner'));
    }

    public static function getMainMenuCatalog()
    {
        $mainMenuCatalog = self::active()
            ->bannerFields()
            ->where('position', 'main_menu_catalog')
            ->with('media')
            ->orderByPriority()
            ->first();

        return view('banners.main-menu-catalog', compact('mainMenuCatalog'));
    }

    public static function getCatalogMob()
    {
        $mobCatalogBanner = self::active()
            ->bannerFields()
            ->where('position', 'catalog_mob')
            ->with('media')
            ->orderByPriority()
            ->first();

        return view('banners.catalog-mob', compact('mobCatalogBanner'));
    }

    /**
     * Scope a query to only active banners.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(fn($query) => $query->where('start_datetime', '<', now())
                ->orWhereNull('start_datetime'))
            ->where(fn($query) => $query->where('end_datetime', '>=', now())
                ->orWhereNull('end_datetime'));
    }

    /**
    * Scope a query to only banner fields.
    *
    * @param  \Illuminate\Database\Eloquent\Builder  $query
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeBannerFields($query) {
      return $query->select('id', 'title', 'url', 'timer', 'spoiler');
    }

    /**
     * Scope a query to order by priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw(
            $query->getGrammar()->compileRandom('') . ' * ( priority + 2 ) DESC'
        );
    }
}
