<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Feedback extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
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
     * Max media sizes
     */
    final const MAX_PHOTO_SIZE = 5_242_880; // 5 MB

    final const MAX_VIDEO_SIZE = 52_428_800; // 50 MB

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
     * Ответы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(FeedbackAnswer::class)->with('media');
    }

    /**
     * Товары
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    protected static function getType($type)
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
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150);
        $this->addMediaConversion('full')->width(2000);
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
}
