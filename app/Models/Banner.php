<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model implements HasMedia
{
    use HasFactory,
        InteractsWithMedia;

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
        $banners = collect([
            'main' => 'main.jpg',
            'main_mobile' => 'main_mobile.jpg',
        ])->concat(
            collect([
                'category_01.jpg',
                'category_02.jpg',
                'category_03.jpg',
                'category_04.jpg',
                'category_05.jpg',
            ])->random(3)
        );
        return view('banners.index-main', compact('banners'));
    }

    public static function getIndexBottom()
    {
        $banners = [
            'left' => 'season_fall.jpg',
            'right' => 'season_winter.jpg',
        ];
        return view('banners.index-bottom', compact('banners'));
    }

    public static function getCatalogTop()
    {
        return view('banners.catalog-top', ['banner' => 'catalog_top.jpg']);
    }
}
