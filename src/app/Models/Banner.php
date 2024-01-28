<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string|null $position
 * @property string|null $title
 * @property string|null $url
 * @property int $priority
 * @property bool $active
 * @property string|null $start_datetime
 * @property string|null $end_datetime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool|null $show_timer
 * @property array|null $spoiler
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Banner active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Banner bannerFields()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Banner orderByPriority()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Banner extends Model implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        SoftDeletes;

    protected $casts = [
        'spoiler' => 'json',
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

    public static function getFeedback()
    {
        $feedbackBanner = self::active()
            ->bannerFields()
            ->where('position', 'feedback')
            ->with('media')
            ->orderByPriority()
            ->first();

        return view('banners.feedback', compact('feedbackBanner'));
    }

    public static function getFeedbackMob()
    {
        $feedbackBannerMob = self::active()
            ->bannerFields()
            ->where('position', 'feedback_mob')
            ->with('media')
            ->orderByPriority()
            ->first();

        return view('banners.feedback-mob', compact('feedbackBannerMob'));
    }

    /**
     * Get spoiler text color (Hex).
     */
    public function getSpoilerTextColor(): string
    {
        return $this->spoiler['text_color'] ?? '#fff';
    }

    /**
     * Get spoiler background color (Hex).
     */
    public function getSpoilerBgColor(): string
    {
        return $this->spoiler['bg_color'] ?? '#d22020';
    }

    public function setShowTimerAttribute($value)
    {
        $this->attributes['show_timer'] = $this->end_datetime ? $value : false;
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
            ->where(fn ($query) => $query->where('start_datetime', '<', now())
                ->orWhereNull('start_datetime'))
            ->where(fn ($query) => $query->where('end_datetime', '>=', now())
                ->orWhereNull('end_datetime'));
    }

    /**
     * Scope a query to only banner fields.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBannerFields($query)
    {
        return $query->select('id', 'title', 'url', 'end_datetime', 'show_timer', 'spoiler');
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
