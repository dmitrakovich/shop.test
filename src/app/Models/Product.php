<?php

namespace App\Models;

use App\Enums\Product\ProductLabels;
use App\Facades\Currency;
use App\Services\SearchService;
use App\Traits\ProductSales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product
 *
 * @property int $id
 * @property Category $category
 * @property Brand $brand
 * @property Collection<Size> $sizes
 * @property string $sku (new title)
 * @property string $slug
 * @property float $price
 * @property float $old_price
 * @property int $category_id
 * @property string $color_txt
 * @property string $fabric_top_txt
 * @property string $fabric_inner_txt
 * @property string $fabric_insole_txt
 * @property string $fabric_outsole_txt
 * @property string $heel_txt
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|AvailableSizes[] $availableSizes
 * ...
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
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Ссылка на товар
     */
    protected ?string $url = null;

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
        return $this->belongsTo(Collection::class);
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
     * Страна производитель
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
        return $this->url ?? ($this->url = $this->category->getUrl() . '/' . $this->slug);
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
     *
     * @return float
     */
    public function getFormattedPrice()
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
     * Get fianl old price after apply other sales
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
     *
     * @return float
     */
    public function getFormattedOldPrice()
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
     * Checks that the product has only one size
     */
    public function hasOneSize(): bool
    {
        return $this->sizes->count() === 1 && $this->sizes->first()->slug === Size::ONE_SIZE_SLUG;
    }

    /**
     * Check min installmnet price
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
