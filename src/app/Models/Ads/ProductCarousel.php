<?php

namespace App\Models\Ads;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson;

/**
 * @property int $id
 * @property string|null $title
 * @property array|null $categories
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
 *
 * @property-read \App\Models\Category|null $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categoriesList
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ads\ProductCarousel ordered(string $direction = 'asc')
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
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
            Cache::forget($cacheConfig['product_carousel_recent_products']['key'] ?? ' ');
        });
    }

    protected $appends = [
        'categories_list',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
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
     */
    public function categories(): BelongsToJson
    {
        return $this->belongsToJson(Category::class, 'categories');
    }

    /**
     * Categories products
     */
    public function categoriesList(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get category ids
     */
    public function getCategoryIds(): array
    {
        $categories = [];
        foreach ($this->categories as $category_id) {
            $categories = array_merge(
                $categories,
                Category::getChildrenCategoriesIdsList($category_id)
            );
        }

        return array_unique($categories);
    }

    /**
     * Get simple carousels
     *
     * @return Collection|ProductCarousel[]
     */
    public static function getSimpleCarousels(): Collection
    {
        return self::ordered()
            ->where('is_imidj', false)
            ->whereNull('enum_type_id')
            ->get();
    }

    /**
     * Get imidj carousel
     */
    public static function getImidjCarousel(): ?self
    {
        return self::query()->where('is_imidj', true)->first();
    }
}
