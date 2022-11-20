<?php

namespace App\Models\Ads;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class ProductCarousel extends Model implements Sortable
{
    use SortableTrait;
    use HasFactory;
    use HasJsonRelationships;

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
    public function categories()
    {
        return $this->belongsToJson(Category::class, 'categories');
    }

    /**
     * Categories products
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categoriesList()
    {
        return $this->belongsToMany(Category::class);
    }
}
