<?php

namespace App\Models\Ads;

use App\Enums\Ads\BannerMediaCollection;
use App\Enums\Ads\BannerPosition;
use App\Enums\Ads\BannerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property BannerPosition $position
 * @property BannerType $type
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
 * @property array<array-key, mixed>|null $spoiler
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static Builder<self> active()
 * @method static Builder<self> bannerFields()
 * @method static Builder<self> orderByPriority()
 */
class Banner extends Model implements HasMedia
{
    use InteractsWithMedia,
        SoftDeletes;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    protected $casts = [
        'position' => BannerPosition::class,
        'type' => BannerType::class,
        'active' => 'boolean',
        'show_timer' => 'boolean',
        'spoiler' => 'json',
    ];

    final const array ACCEPTED_VIDEO_TYPES = [
        'video/mp4',
        'video/webm',
        'video/ogg',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(BannerMediaCollection::DESKTOP_IMAGE->value)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')->format('jpg')->height(40);
            });

        $this
            ->addMediaCollection(BannerMediaCollection::MOBILE_IMAGE->value)
            ->singleFile();

        $this
            ->addMediaCollection(BannerMediaCollection::DESKTOP_VIDEO->value)
            ->singleFile();

        $this
            ->addMediaCollection(BannerMediaCollection::MOBILE_VIDEO->value)
            ->singleFile();

        $this
            ->addMediaCollection(BannerMediaCollection::DESKTOP_VIDEO_PREVIEW->value)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')->format('jpg')->height(40);
            });

        $this
            ->addMediaCollection(BannerMediaCollection::MOBILE_VIDEO_PREVIEW->value)
            ->singleFile();
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

    /**
     * Scope a query to only active banners.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
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
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeBannerFields(Builder $query): Builder
    {
        return $query->select('id', 'title', 'url', 'end_datetime', 'show_timer', 'spoiler');
    }

    /**
     * Scope a query to order by priority.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderByRaw(
            $query->getGrammar()->compileRandom('') . ' * ( priority + 2 ) DESC'
        );
    }
}
