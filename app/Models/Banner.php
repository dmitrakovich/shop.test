<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    /**
     * Картинки баннеров
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Images::class, 'entity')->orderBy('sorting');
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
