<?php

namespace App\Models;

use App\Enums\StockTypeEnum;
use App\Models\Bots\Telegram\TelegramChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read TelegramChat $chat
 */
class Stock extends Model implements HasMedia, Sortable
{
    use HasFactory;
    use InteractsWithMedia, SortableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => StockTypeEnum::class,
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
}
