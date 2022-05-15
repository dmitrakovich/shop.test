<?php

namespace App\Admin\Models;

use App\Models\Product as ProductModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
    public function path(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getUrl()
        );
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

    /**
     * @return void
     */
    public function setPhotos(array $photos)
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
     * @return array
     */
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
