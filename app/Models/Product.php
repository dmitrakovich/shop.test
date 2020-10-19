<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
class Product extends Model
{
    use SoftDeletes;
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
    /**
     * Картинки товара
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Images::class, 'entity')->orderBy('sorting');
    }
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
     * Получить полное название продукта
     *
     * @return void
     */
    public function getFullName()
    {
        return ($this->brand->name ?? 'VITACCI'). ' ' . $this['name_ru-RU'];
    }
}
