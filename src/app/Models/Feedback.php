<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Feedback
 *
 * @property int $id
 * @property int $user_id
 * @property int $yandex_id
 * @property string $user_name
 * @property string $user_email
 * @property string $user_phone
 * @property string $user_city
 * @property string $text
 * @property int $rating
 * @property int $product_id
 * @property int $type_id
 * @property int $captcha_score
 * @property bool $view_only_posted
 * @property bool $publish
 * @property string $ip
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property EloquentCollection<FeedbackAnswer> $answers
 * @property ?Product $product
 */
class Feedback extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_city',
        'text',
        'rating',
        'product_id',
        'type_id',
        'view_only_posted',
        'publish',
        'ip',
    ];

    /**
     * Feedbacks types by ids
     */
    final const TYPE_SPAM = 0;

    final const TYPE_REVIEW = 1;

    final const TYPE_QUESTION = 2;

    /**
     * Тип по умолчанию
     *
     * @var string
     */
    protected const DEFAULT_TYPE = 'reviews';

    /**
     * Доступные типы обратной связи
     *
     * @var array
     */
    protected static $availableTypes = [
        'reviews',
        'models',
        'questions',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['photos', 'videos'];

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
     * Check & return feedback type
     *
     * @param  mixed  $type
     */
    public static function getType($type): string
    {
        return in_array($type, self::$availableTypes) ? $type : self::DEFAULT_TYPE;
    }

    public function scopeType($query, $type)
    {
        return match (self::getType($type)) {
            'reviews' => $query->where('type_id', 1),
            'models' => $query->where('product_id', '>', 0),
            'questions' => $query->where('type_id', 2),
        };
    }

    /**
     * Отзывы для товаров
     *
     * @param [type] $query
     * @param  int  $productId идентификатор товара
     * @return void
     */
    public function scopeForProduct($query, int $productId)
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

    /**
     * Farmat date in admin panel
     *
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }

    /**
     * Photos accessor
     */
    public function getPhotosAttribute(): Collection
    {
        return $this->getMedia('photos')->map(fn (Media $media) => $media->getUrl());
    }

    /**
     * Videos accessor
     */
    public function getVideosAttribute(): Collection
    {
        return $this->getMedia('videos')->map(fn (Media $media) => $media->getUrl());
    }
}
