<?php

namespace App\Traits;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ProductMedia
{
    use InteractsWithMedia;

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->format('jpg')->width(100);
        $this->addMediaConversion('catalog')->format('jpg')->width(300);
        $this->addMediaConversion('normal')->format('jpg')->width(700);

        $this->addMediaConversion('thumb-webp')->format('webp')->width(100);
        $this->addMediaConversion('catalog-webp')->format('webp')->width(300);
        $this->addMediaConversion('normal-webp')->format('webp')->width(700);
    }

    /**
     * Get the fallback media URL.
     */
    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb' => asset('images/no-image-100.png'),
            'catalog' => asset('images/no-image-300.png'),
            default => asset('images/no-image.png'),
        };
    }

    /**
     * Get the fallback media path.
     */
    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb' => public_path('images/no-image-100.png'),
            'catalog' => public_path('images/no-image-300.png'),
            default => public_path('images/no-image.png'),
        };
    }

    /**
     * Get the first catalog media URL.
     */
    public function getFirstCatalogMediaUrl(): string
    {
        return $this->getFirstMediaUrl('default', 'catalog');
    }

    /**
     * Get the first imidj media URL.
     */
    public function getFirstImidjMediaUrl(): string
    {
        return $this->getMedia('default', ['is_imidj' => true])->first()->getUrl('normal');
    }
}
