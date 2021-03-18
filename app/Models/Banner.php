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

    protected $appends = [
        'resource',
    ];

    public function setResourceAttribute($resource)
    {
        $this->addMedia(public_path("uploads/$resource"))
            ->toMediaCollection();
    }

    public function getResourceAttribute()
    {
        return $this->getFirstMediaUrl();
    }

    public static function getIndexMain()
    {
        $indexTopBanners = self::active()
            ->where('position', 'index_top')
            ->with('media')
            // ->inRandomOrder()
            // ->limit(3)
            ->get(['id', 'title', 'url']);

        $banners = [
            'main' => 'main.jpg',
            'main_mobile' => 'main_mobile.jpg',
            'index_top' => $indexTopBanners,
        ];
        return view('banners.index-main', compact('banners'));
    }

    public static function getIndexBottom()
    {
        $indexBottomBanners = self::active()
            ->where('position', 'index_bottom')
            ->with('media')
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
