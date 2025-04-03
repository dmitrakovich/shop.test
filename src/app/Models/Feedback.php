<?php

namespace App\Models;

use App\Enums\Feedback\FeedbackType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $device_id
 * @property string $user_name
 * @property string|null $user_city
 * @property string $text
 * @property int $rating
 * @property int|null $product_id
 * @property \App\Enums\Feedback\FeedbackType $type
 * @property int $captcha_score
 * @property bool $publish
 * @property string $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Collection $photos
 * @property \Illuminate\Support\Collection $videos
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedbackAnswer[] $answers
 * @property-read \App\Models\Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback type($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback forProduct(int $productId)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Feedback extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'feedbacks';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publish' => 'boolean',
        'type' => FeedbackType::class,
    ];

    protected $fillable = [
        'user_id',
        'device_id',
        'user_name',
        'user_city',
        'text',
        'rating',
        'product_id',
        'type',
        'captcha_score',
        'publish',
        'ip',
    ];

    /**
     * Feedback related answers
     */
    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackAnswer::class)->with('media');
    }

    /**
     * Product related feedback.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Отзывы для товаров
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Размеры изображений
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('photos')
            ->width(150)->height(150);
        $this->addMediaConversion('full')
            ->performOnCollections('photos')
            ->width(2000);

        $this->addMediaConversion('thumb')
            ->performOnCollections('videos')
            ->extractVideoFrameAtSecond(2)
            ->width(150)->height(150);
    }
}
