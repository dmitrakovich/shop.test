<?php

namespace App\Models;

use App\Enums\Product\ProductLabels;
use App\Facades\Currency;
use App\Models\Collection as ProductCollection;
use App\Models\OneC\Product as ProductFromOneC;
use App\Services\SearchService;
use App\Traits\ProductSales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\GoogleTagManager\DataLayer;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int|null $one_c_id
 * @property string $slug
 * @property string $old_slug
 * @property string $sku
 * @property int $label_id
 * @property float $buy_price
 * @property float $price
 * @property float $old_price
 * @property int $category_id
 * @property int $season_id
 * @property int $brand_id
 * @property int $manufacturer_id
 * @property int $collection_id
 * @property string|null $color_txt
 * @property string|null $fabric_top_txt
 * @property string|null $fabric_inner_txt
 * @property string|null $fabric_insole_txt
 * @property string|null $fabric_outsole_txt
 * @property string|null $heel_txt
 * @property string|null $bootleg_height_txt
 * @property string|null $description
 * @property bool $action
 * @property int $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $product_group_id ID группы товаров
 * @property string|null $key_features Ключевая особенность товара
 * @property int|null $country_of_origin_id
 *
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Collection|null $collection
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Size[] $sizes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Color[] $colors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Fabric[] $fabrics
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Heel[] $heels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Style[] $styles
 * @property-read \App\Models\Season|null $season
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\ProductAttributes\Manufacturer|null $manufacturer
 * @property-read \App\Models\ProductAttributes\CountryOfOrigin|null $countryOfOrigin
 * @property-read \App\Models\Url|null $url
 * @property-read \App\Models\Favorite|null $favorite
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvailableSizes[] $availableSizes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product sorting(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product search(?string $search = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product onlyWithDiscount(float $amount = 0.01)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product onlyNew(int $days = 10)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    use ProductSales;
    use SoftDeletes {
        restore as restoreSoftDeletes;
    }

    /**
     * Default sorting
     */
    public const DEFAULT_SORT = 'rating';

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Ссылка на товар
     */
    protected ?string $url = null;

    /**
     * DataLayer for product
     *
     * @todo remove from product Model
     */
    public DataLayer $dataLayer;

    /**
     * Категория товара
     */
    public function category(): Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * коллекция
     */
    public function collection(): Relations\BelongsTo
    {
        return $this->belongsTo(ProductCollection::class);
    }

    /**
     * Размеры
     */
    public function sizes(): Relations\MorphToMany
    {
        return $this->morphedByMany(Size::class, 'attribute', 'product_attributes');
    }

    /**
     * Цвет
     */
    public function colors(): Relations\MorphToMany
    {
        return $this->morphedByMany(Color::class, 'attribute', 'product_attributes');
    }

    /**
     * материалы
     */
    public function fabrics(): Relations\MorphToMany
    {
        return $this->morphedByMany(Fabric::class, 'attribute', 'product_attributes');
    }

    /**
     * Типы каблука
     */
    public function heels(): Relations\MorphToMany
    {
        return $this->morphedByMany(Heel::class, 'attribute', 'product_attributes');
    }

    /**
     * Стили
     */
    public function styles(): Relations\MorphToMany
    {
        return $this->morphedByMany(Style::class, 'attribute', 'product_attributes');
    }

    /**
     * Сезон
     */
    public function season(): Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Теги
     */
    public function tags(): Relations\MorphToMany
    {
        return $this->morphedByMany(Tag::class, 'attribute', 'product_attributes');
    }

    /**
     * Бренд
     */
    public function brand(): Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Производитель
     */
    public function manufacturer(): Relations\BelongsTo
    {
        return $this->belongsTo(ProductAttributes\Manufacturer::class);
    }

    /**
     * Country of origin
     */
    public function countryOfOrigin(): Relations\BelongsTo
    {
        return $this->belongsTo(ProductAttributes\CountryOfOrigin::class);
    }

    /**
     * Slug для фильтра
     */
    public function url(): Relations\MorphOne
    {
        return $this->morphOne(Url::class, 'model');
    }

    /**
     * Get the favorite associated with the product.
     */
    public function favorite(): Relations\HasOne
    {
        return $this->hasOne(Favorite::class);
    }

    /**
     * Product group relation.
     */
    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    /**
     * Get the available sizes associated with the product.
     */
    public function availableSizes(): Relations\HasMany
    {
        return $this->hasMany(AvailableSizes::class);
    }

    /**
     * Get the product from 1C associated with the site product.
     */
    public function productFromOneC(): Relations\BelongsTo
    {
        return $this->belongsTo(ProductFromOneC::class, 'one_c_id', 'CODE');
    }

    /**
     * Get product simple name (category name + brand name)
     */
    public function simpleName(): string
    {
        return $this->category->title . ' ' . $this->brand->name;
    }

    /**
     * Get product short name (category name + id)
     */
    public function shortName(): string
    {
        return $this->category->title . ' ' . $this->id;
    }

    /**
     * Simple name + id
     */
    public function extendedName(): string
    {
        return $this->simpleName() . ' ' . $this->id;
    }

    /**
     * Получить полное название продукта
     */
    public function getFullName(): string
    {
        return $this->brand->name . ' ' . $this->sku;
    }

    /**
     * Получить ссылку на товар
     */
    public function getUrl(): string
    {
        return route('product.show', $this->slug);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100);
        $this->addMediaConversion('catalog')->width(300);
        $this->addMediaConversion('normal')->width(700);
        $this->addMediaConversion('full')->width(1200);
    }

    /**
     * Get the fallback media URL.
     */
    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb' => asset('/images/no-image-100.png'),
            'catalog' => asset('/images/no-image-300.png'),
            default => asset('/images/no-image.png'),
        };
    }

    /**
     * Get the fallback media path.
     */
    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb' => public_path('/images/no-image-100.png'),
            'catalog' => public_path('/images/no-image-300.png'),
            default => public_path('/images/no-image.png'),
        };
    }

    /**
     * Сортировка товаров
     *
     * @return Builder
     */
    public function scopeSorting(Builder $query, string $type)
    {
        return match ($type) {
            'newness' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'price-up' => $query->orderBy('price')->orderBy('id'),
            'price-down' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('rating')->orderByDesc('id'), // rating
            // 'discount' => $query->orderByDesc('discount')->orderByDesc('id'),
        };
    }

    /**
     * Поиск товаров
     *
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search = null)
    {
        if (empty($search)) {
            return $query;
        }
        $searchService = new SearchService($search);

        if ($searchService->useSimpleSearch()) {
            $searchValue = $searchService->getIds()[0];

            return $searchService->generateSearchQuery($query, 'sku')
                ->orWhere('id', $searchValue);
        }

        $query->where(function ($query) use ($searchService) {
            $searchService->generateSearchQuery($query, 'sku')
                ->orWhereIn('id', $searchService->getIds())
                ->orWhereHas('brand', function (Builder $query) use ($searchService) {
                    $searchService->generateSearchQuery($query, 'name');
                })
                ->orWhereHas('category', function (Builder $query) use ($searchService) {
                    $searchService->generateSearchQuery($query, 'title');
                })
                ->orWhere(function (Builder $query) use ($searchService) {
                    $searchService->generateSearchQuery($query, 'color_txt');
                })
                ->orWhereHas('tags', function (Builder $query) use ($searchService) {
                    $searchService->generateSearchQuery($query, 'name');
                });
        });

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get only products with discount
     *
     * @return Builder
     */
    public function scopeOnlyWithDiscount(Builder $query, float $amount = 0.01)
    {
        return $query->whereRaw('((`old_price` - `price`) / `old_price`) > ?', $amount);
    }

    /**
     * Get only new products
     *
     * @return Builder
     */
    public function scopeOnlyNew(Builder $query, int $days = 10)
    {
        // return $query->where('created_at', '>', now()->subDays($days));
        return $query->where('old_price', 0);
    }

    /**
     * Check product's discount
     */
    public function hasDiscount(): bool
    {
        return $this->getPrice() < $this->getOldPrice();
    }

    /**
     * Get product price
     */
    public function getPrice(?string $currencyCode = null): float
    {
        return Currency::convert($this->getFinalPrice(), $currencyCode);
    }

    /**
     * get product price
     */
    public function getFormattedPrice(): string
    {
        return Currency::convertAndFormat($this->getFinalPrice());
    }

    /**
     * Get fixed wrong old price
     */
    public function getFixedOldPrice(): float
    {
        return $this->old_price > $this->price ? $this->old_price : $this->price;
    }

    /**
     * Get final old price after apply other sales
     */
    public function getFinalOldPrice(): float
    {
        $this->applySales();

        return $this->getFixedOldPrice();
    }

    /**
     * Get product old price
     */
    public function getOldPrice(?string $currencyCode = null): float
    {
        return Currency::convert($this->getFinalOldPrice(), $currencyCode);
    }

    /**
     * get product old price
     */
    public function getFormattedOldPrice(): string
    {
        return Currency::convertAndFormat($this->getFinalOldPrice());
    }

    /**
     * Set default values for product
     *
     * @return void
     */
    public function setDefaultValues(int $id = 0)
    {
        $this->id = $id;
        $this->sku = 'Товар удалён';
        $this->deleted_at = $this->created_at = Carbon::createFromDate(2017);

        $this->setRelation('category', Category::getDefault());
        $this->setRelation('brand', Brand::getDefault());
    }

    /**
     * Is the model new
     */
    public function isNew(): bool
    {
        return $this->old_price == 0;
    }

    /**
     * Is the product in favorite list
     */
    public function isFavorite(): bool
    {
        return isset($this->favorite);
    }

    /**
     * Checks that the product has only one size
     */
    public function hasOneSize(): bool
    {
        return $this->sizes->count() === 1 && $this->sizes->first()->slug === Size::ONE_SIZE_SLUG;
    }

    /**
     * Check min installment price
     */
    public function availableInstallment(): bool
    {
        return $this->getPrice() >= Config::findCacheable('installment')['min_price'];
    }

    /**
     * Returns an array of product labels that should be excluded during processing.
     */
    public static function excludedLabels(): array
    {
        return [
            ProductLabels::DO_NOT_PUBLISH->value,
            ProductLabels::DO_NOT_UPDATE->value,
        ];
    }

    /**
     * Restore a soft-deleted model instance.
     */
    public function restore(): bool
    {
        if ($this->label_id === ProductLabels::DO_NOT_PUBLISH->value) {
            $this->label_id = ProductLabels::NONE->value;
            $this->save();
        }

        return $this->restoreSoftDeletes();
    }
}
