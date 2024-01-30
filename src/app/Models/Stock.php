<?php

namespace App\Models;

use App\Enums\StockTypeEnum;
use App\Models\Bots\Telegram\TelegramChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int|null $one_c_id
 * @property int $city_id
 * @property int|null $private_chat_id
 * @property int|null $group_chat_id
 * @property \Illuminate\Support\Carbon|null $offline_notifications_pause_until
 * @property \App\Enums\StockTypeEnum $type
 * @property string $name
 * @property string $internal_name
 * @property string|null $description
 * @property string|null $address
 * @property string|null $worktime Рабочее время
 * @property string|null $phone
 * @property bool $has_pickup
 * @property float|null $geo_latitude
 * @property float|null $geo_longitude
 * @property bool $check_availability
 * @property int $sorting
 * @property int $site_sorting Сортировка на сайте
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $photos
 *
 * @property-read \App\Models\City|null $city
 * @property-read \App\Models\Bots\Telegram\TelegramChat|null $privateChat
 * @property-read \App\Models\Bots\Telegram\TelegramChat|null $groupChat
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock ordered(string $direction = 'asc')
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Stock extends Model implements HasMedia, Sortable
{
    use HasFactory;
    use InteractsWithMedia, SortableTrait;

    /**
     * Temp constant for Minsk stock id
     */
    const MINKS_ID = 17;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => StockTypeEnum::class,
        'offline_notifications_pause_until' => 'datetime',
    ];

    public $sortable = [
        'order_column_name' => 'sorting',
        'sort_when_creating' => true,
    ];

    protected $appends = [
        'photos',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sorting', 'asc');
        });
    }

    /**
     * City
     */
    public function city(): Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Private chat for notifications
     */
    public function privateChat(): Relations\BelongsTo
    {
        return $this->belongsTo(TelegramChat::class);
    }

    /**
     * Group chat for notifications
     */
    public function groupChat(): Relations\BelongsTo
    {
        return $this->belongsTo(TelegramChat::class);
    }

    /**
     * Photos mutator
     *
     * @param  array  $photos
     */
    public function setPhotosAttribute(array $photos)
    {
        $currentPhotos = [];
        $mediaPointer = [];
        $mediaItems = $this->getMedia();

        foreach ($mediaItems as $key => $image) {
            $url = $image->getUrl();
            $currentPhotos[] = $url;
            $mediaPointer[$url] = $key;
        }
        $newPhotos = array_diff($photos, $currentPhotos);
        $oldPhotos = array_diff($currentPhotos, $photos);

        foreach ($newPhotos as $photo) {
            $this->addMedia(public_path("uploads/$photo"))->toMediaCollection();
        }

        foreach ($oldPhotos as $photo) {
            $key = $mediaPointer[$photo];
            $mediaItems[$key]->delete();
        }
    }

    /**
     * Photos accessor
     *
     * @return string
     */
    public function getPhotosAttribute()
    {
        return $this->getMedia()->map(fn ($media) => $media->getUrl());
    }

    /**
     * Set a pause for offline order notifications and return the new pause time.
     */
    public function setOfflineNotificationsPause(int $minutes): Carbon
    {
        $newPauseUntil = now()->addMinutes($minutes);

        $this->offline_notifications_pause_until = $newPauseUntil;
        $this->save();

        return $newPauseUntil;
    }

    /**
     * Check if offline order notifications are paused relative to the current time.
     */
    public function areOfflineNotificationsPaused(): bool
    {
        return $this->offline_notifications_pause_until !== null
            && $this->offline_notifications_pause_until->isFuture();
    }
}
