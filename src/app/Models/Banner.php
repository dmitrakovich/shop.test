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

    public static function getIndexMain()
    {
        $indexMainBanner = self::active()
            ->where('position', 'index_main')
            ->with('media')
            ->orderByPriority()
            ->first(['id', 'title', 'url']);

        $indexTopBanners = self::active()
            ->where('position', 'index_top')
            ->with('media')
            ->orderByPriority()
            ->limit(3)
            ->get(['id', 'title', 'url']);

        return view('banners.index-main', compact('indexMainBanner', 'indexTopBanners'));
    }

    public static function getIndexBottom()
    {
        $indexBottomBanners = self::active()
            ->where('position', 'index_bottom')
            ->with('media')
            ->orderByDesc('priority')
            ->get(['id', 'title', 'url']);

        return view('banners.index-bottom', compact('indexBottomBanners'));
    }

    public static function getCatalogTop()
    {
        $catalogBanner = self::active()
            ->where('position', 'catalog_top')
            ->with('media')
            ->orderByPriority()
            ->first(['id', 'title', 'url']);

        return view('banners.catalog-top', compact('catalogBanner'));
    }

    public static function getMainMenuCatalog()
    {
        $mainMenuCatalog = self::active()
            ->where('position', 'main_menu_catalog')
            ->with('media')
            ->orderByPriority()
            ->first(['id', 'title', 'url']);

        return view('banners.main-menu-catalog', compact('mainMenuCatalog'));
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
