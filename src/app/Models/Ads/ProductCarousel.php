<?php

namespace App\Models\Ads;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @property int $id
 * @property string|null $title
 * @property bool $is_imidj
 * @property bool $only_sale
 * @property bool $only_new
 * @property int $speed
 * @property int $count
 * @property bool $sorting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool|null $enum_type_id
 * @property array|null $additional_settings Дополнительные настройки
 * @property mixed $categories_list
 * @property \App\Models\Category|null $categories
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categoriesList
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ads\ProductCarousel ordered(string $direction = 'asc')
 */
class ProductCarousel extends Model implements Sortable
{
    use HasFactory;
    use HasJsonRelationships;
    use SortableTrait;

    public static function boot()
    {
        parent::boot();
        self::saved(function ($model) {
            $cacheConfig = config('cache_config');
            Cache::forget($cacheConfig['product_carousel_similar_products']['key'] ?? '');
            Cache::forget($cacheConfig['product_carousel_recent_products']['key'] ?? ' ');
        });
    }

    protected $appends = [
        'categories_list',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'additional_settings' => 'array',
        'categories' => 'json',
        'only_sale' => 'bool',
        'only_new' => 'bool',
    ];

    public $sortable = [
        'order_column_name' => 'sorting',
        'sort_when_creating' => true,
    ];

    public function getCategoriesListAttribute()
    {
        return $this->categories;
    }

    public function setCategoriesListAttribute($value)
    {
        $this->attributes['categories'] = json_encode(array_values(array_map('intval', array_filter($value))));
    }

    /**
     * Categories products
     *
     * @return \Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson
     */
    public function categories(): BelongsToJson
    {
        return $this->belongsToJson(Category::class, 'categories');
    }

    /**
     * Categories products
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categoriesList(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
