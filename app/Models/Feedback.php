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
     * Ответы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(FeedbackAnswer::class)->with('photos');
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
        switch (self::getType($type)) {
            case 'reviews':
            default:
                return $query->where('type_id', 1);

            case 'models':
                return $query->where('product_id', '>', 0);

            case 'questions':
                return $query->where('type_id', 2);
        }
    }
    /**
     * Отзывы для товаров
     *
     * @param [type] $query
     * @param integer $productId идентификатор товара
     * @return void
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
    /**
     * Размеры изображений
     *
     * @param Media $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150);
        $this->addMediaConversion('full')->width(2000);
    }
    /**
     * Farmat date in admin panel
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
