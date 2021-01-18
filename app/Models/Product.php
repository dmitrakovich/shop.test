<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product
 *
 * @package App
 *
 * @property \App\Category      $category
 * @property string             $title
 * @property string             $slug
 * ...
 */
class Product extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /*protected $fillable = [
        'title',
        '...',
    ];*/
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * Ссылка на товар
     *
     * @var string
     */
    protected $url = null;
    //
    /**
     * Категория товара
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // /**
    //  * Картинки товара
    //  *
    //  * @return Illuminate\Database\Eloquent\Relations\MorphMany
    //  */
    // public function images()
    // {
    //     return $this->morphMany(Images::class, 'entity')->orderBy('sorting');
    // }
    /**
     * Получить шильды для продукта
     *
     * @return string
     */
    public function getLabelsAttribute()
    {
        return 'labels';
    }
    /**
     * Размеры
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function sizes()
    {
        return $this->morphedByMany(Size::class, 'attribute', 'product_attributes');
    }
    /**
     * Цвет
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    /**
     * материалы
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function fabrics()
    {
        return $this->morphedByMany(Fabric::class, 'attribute', 'product_attributes');
    }
    /**
     * Типы каблука
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function heels()
    {
        return $this->morphedByMany(Heel::class, 'attribute', 'product_attributes');
    }
    /**
     * Стили
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function styles()
    {
        return $this->morphedByMany(Style::class, 'attribute', 'product_attributes');
    }
    /**
     * Сезон
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
    /**
     * Теги
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphedByMany(Tag::class, 'attribute', 'product_attributes');
    }
    /**
     * Бренд
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    /**
     * Slug для фильтра
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function url()
    {
        return $this->morphOne(Url::class, 'model');
    }
    /**
     * Получить полное название продукта
     *
     * @return void
     */
    public function getFullName()
    {
        return ($this->brand->name ?? 'VITACCI'). ' ' . $this['name_ru-RU'];
    }
    /**
     * Получить ссылку на товар
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url ?? ($this->url = $this->category->getUrl() . '/' . $this->slug);
    }
    /**
     * Размеры изображений
     *
     * @param Media $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100);
        $this->addMediaConversion('catalog')->width(300);
        $this->addMediaConversion('normal')->width(700);
        $this->addMediaConversion('full')->width(1200);
    }
    /**
     * Сортировка товаров
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeSorting(Builder $query, string $type)
    {
        switch ($type) {
            case 'newness':
            default:
                return $query->orderBy('created_at', 'desc');

            case 'rating':
                return $query->orderBy('created_at', 'desc');

            case 'price-up':
                return $query->orderBy('price', 'asc');

            case 'price-down':
                return $query->orderBy('price', 'desc');

            case 'sale':
                return $query->orderByRaw('((old_price - price) / old_price) desc');
        }
    }
}
