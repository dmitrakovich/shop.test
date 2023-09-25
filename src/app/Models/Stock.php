<?php

namespace App\Models;

use App\Enums\StockTypeEnum;
use App\Models\Bots\Telegram\TelegramChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use WendellAdriel\Lift\Attributes\Relations\BelongsTo;
use WendellAdriel\Lift\Lift;

/**
 * @property int $id
 * @property Carbon $offline_notifications_pause_until
 * @property string $name
 * @property string $address
 * @property-read City $city City
 * @property-read TelegramChat $privateChat Private chat for notifications
 * @property-read TelegramChat $groupChat Group chat for notifications
 */
#[BelongsTo(City::class)]
#[BelongsTo(TelegramChat::class, 'privateChat', 'private_chat_id')]
#[BelongsTo(TelegramChat::class, 'groupChat', 'group_chat_id')]
class Stock extends Model implements HasMedia, Sortable
{
    use InteractsWithMedia;
    use SortableTrait;
    use Lift;

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
     * Photos mutator
     *
     * @param  string  $resource
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function setPhotosAttribute($photos)
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
