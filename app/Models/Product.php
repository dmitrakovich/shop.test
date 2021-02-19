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
    protected $appends = [
        'path',
        'photos',
    ];
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
    public function colors()
    {
        return $this->morphedByMany(Color::class, 'attribute', 'product_attributes');
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
        return ($this->brand->name ?? 'VITACCI'). ' ' . $this->title;
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
     * Геттер для админки
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return $this->getUrl();
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
     * Сеттер для фоток
     *
     * @param array $photos
     * @return void
     */
    public function setPhotosAttribute($photos)
    {
        $currentPhotos = [];
        $mediaItems = $this->getMedia();
        foreach ($mediaItems as $key => $image) {
            $url = $image->getUrl('catalog');
            $currentPhotos[] = $url;
            $mediaPointer[$url] = $key;
        }

        $path = public_path('uploads');

        $newPhotos = array_diff($photos, $currentPhotos);
        $oldPhotos = array_diff($currentPhotos, $photos);

        foreach ($newPhotos as $photo) {
            $this->addMedia("$path/$photo")->toMediaCollection();
        }

        foreach ($oldPhotos as $photo) {
            $key = $mediaPointer[$photo];
            $mediaItems[$key]->delete();
        }
    }
    /**
     * Геттер для фоток
     *
     * @return array
     */
    public function getPhotosAttribute()
    {
        $photos = [];
        foreach ($this->getMedia() as $image) {
            $photos[] = $image->getUrl('catalog');
            // $photos[] = $image->getPath();
        }
        return $photos;
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
                return $query->orderBy('rating', 'desc');

            case 'price-up':
                return $query->orderBy('price', 'asc');

            case 'price-down':
                return $query->orderBy('price', 'desc');

            case 'sale':
                return $query->orderByRaw('((old_price - price) / old_price) desc');
        }
    }
    /**
     * Поиск товаров
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search = null)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $search = explode(' ', $search);
                $this->generateSearchQuery($query, 'title', $search)
                    ->orWhereHas('brand', function (Builder $query) use ($search) {
                        $this->generateSearchQuery($query, 'name', $search);
                    })
                    ->orWhereHas('category', function (Builder $query) use ($search) {
                        $this->generateSearchQuery($query, 'title', $search);
                    })
                    ->orWhere(function (Builder $query) use ($search) {
                        $this->generateSearchQuery($query, 'color_txt', $search);
                    })
                    ->orWhereHas('tags', function (Builder $query) use ($search) {
                        $this->generateSearchQuery($query, 'name', $search);
                    });
            });
            $query->orderBy('created_at', 'desc');
        }
        return $query;
    }

    /**
     * Сгенерировать query для поиска
     *
     * @param Builder $query
     * @param string $column
     * @param array $search
     * @return Builder
     */
    protected function generateSearchQuery(Builder $query, string $column, array $search)
    {
        $value = current($search);
        $query->where($column, 'like', "%$value%");

        while ($value = next($search)) {
            $query->orWhere($column, 'like', "%$value%");
        }
        return $query;
    }
}
