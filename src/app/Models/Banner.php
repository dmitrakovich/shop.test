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
            ->orderByDesc('priority')
            ->first(['id', 'title', 'url']);

        $indexTopBanners = self::active()
            ->where('position', 'index_top')
            ->with('media')
            ->orderByDesc('priority')
            ->get(['id', 'title', 'url']);

        return view('banners.index-main', compact('indexMainBanner', 'indexTopBanners'));
    }

    public static function getIndexBottom()
    {
        $indexBottomBanners = self::active()
            ->where('position', 'index_bottom')
            ->with('media')
            ->orderByDesc('priority')
            // ->inRandomOrder()
            // ->limit(4)
            ->get(['id', 'title', 'url']);

        return view('banners.index-bottom', compact('indexBottomBanners'));
    }

    public static function getCatalogTop()
    {
        $catalogBanner = self::active()
            ->where('position', 'catalog_top')
            ->with('media')
            ->inRandomOrder()
            ->first(['id', 'title', 'url']);

        return view('banners.catalog-top', compact('catalogBanner'));
    }

    public static function getMainMenuCatalog()
    {
        $mainMenuCatalog = self::active()
            ->where('position', 'main_menu_catalog')
            ->with('media')
            ->inRandomOrder()
            ->first(['id', 'title', 'url']);

        return view('banners.main-menu-catalog', compact('mainMenuCatalog'));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($query) {
                return $query->where('start_datetime', '<', now())
                    ->orWhereNull('start_datetime');
            })
            ->where(function ($query) {
                return $query->where('end_datetime', '>=', now())
                    ->orWhereNull('end_datetime');
            });
    }
}
