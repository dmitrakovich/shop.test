<?php

namespace App\Admin\Models;

use App\Models\Product as ProductModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property string $path
 * @property mixed $photos
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Product sorting(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Product search(?string $search = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Product onlyWithDiscount(float $amount = 0.01)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Product onlyNew(int $days = 10)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Product extends ProductModel
{
    protected $appends = [
        'path',
        'photos',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        //
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return ProductModel::class;
    }

    /**
     * Accessor for admin panel
     */
    public function getPathAttribute(): string
    {
        return $this->getUrl();
    }

    /**
     * Interact with the product's photos.
     */
    public function photos(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getPhotos(),
            set: fn ($photos) => $this->setPhotos($photos)
        );
    }

    public function setPhotos(array $photos): void
    {
        $currentPhotos = [];
        $mediaItems = $this->getMedia();
        foreach ($mediaItems as $key => $image) {
            $url = $image->getUrl();
            $currentPhotos[] = $url;
            $mediaPointer[$url] = $key;
        }

        $path = public_path('uploads');

        $newPhotos = array_diff($photos, $currentPhotos);
        $oldPhotos = array_diff($currentPhotos, $photos);

        // sorting (hotfix)
        if (request()->has('_file_sort_') && empty($newPhotos)) {
            $ordering = [];
            foreach ($photos as $value) {
                $ordering[] = $mediaItems[$mediaPointer[$value]]->id;
            }
            Media::setNewOrder($ordering);

            return;
        }

        foreach ($newPhotos as $photo) {
            $this->addMedia("$path/$photo")->toMediaCollection();
        }

        foreach ($oldPhotos as $photo) {
            $key = $mediaPointer[$photo];
            $mediaItems[$key]->delete();
        }
    }

    public function getPhotos(): array
    {
        $photos = [];
        foreach ($this->getMedia() as $image) {
            $photos[] = $image->getUrl();
            // $photos[] = $image->getUrl('catalog');
            // $photos[] = $image->getPath();
        }

        return $photos;
    }
}
