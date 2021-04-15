<?php

namespace App\Admin\Models;

use App\Models\Product as ProductModel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends ProductModel
{
    // use InteractsWithMedia;

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
        static::deleting(function(self $product) {
            $product->url()->delete();
        });
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
     * Геттер для админки
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return $this->getUrl();
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
            return Media::setNewOrder($ordering);
        }

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
            $photos[] = $image->getUrl();
            // $photos[] = $image->getUrl('catalog');
            // $photos[] = $image->getPath();
        }
        return $photos;
    }
}
