<?php

namespace App\Traits;

use Spatie\Image\Enums\Constraint;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ProductMedia
{
    use InteractsWithMedia;

    /**
     * Target max width per conversion (height follows aspect ratio). Never upscales beyond the original.
     *
     * @var array<string, positive-int>
     */
    private const PRODUCT_IMAGE_MAX_WIDTHS = [
        'thumb' => 200,
        'small' => 480,
        'medium' => 720,
        'large' => 1080,
        'xlarge' => 1600,
        'zoom' => 2400,
    ];

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $constraints = [Constraint::PreserveAspectRatio, Constraint::DoNotUpsize];

        foreach (self::PRODUCT_IMAGE_MAX_WIDTHS as $name => $maxWidth) {
            $this->addMediaConversion($name)
                ->format('jpg')
                ->width($maxWidth, $constraints);

            $this->addMediaConversion("{$name}-webp")
                ->format('webp')
                ->width($maxWidth, $constraints);
        }
    }

    /**
     * Get the fallback media URL.
     */
    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb', 'thumb-webp' => asset('images/no-image-100.png'),
            'small', 'small-webp' => asset('images/no-image-300.png'),
            default => asset('images/no-image.png'),
        };
    }

    /**
     * Get the fallback media path.
     */
    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        return match ($conversionName) {
            'thumb', 'thumb-webp' => public_path('images/no-image-100.png'),
            'small', 'small-webp' => public_path('images/no-image-300.png'),
            default => public_path('images/no-image.png'),
        };
    }

    /**
     * Get the first catalog media URL.
     */
    public function getFirstCatalogMediaUrl(): string
    {
        return $this->getFirstMediaUrl('default', 'small');
    }

    /**
     * Get the first imidj media URL.
     */
    public function getFirstImidjMediaUrl(): string
    {
        return $this->getMedia('default', ['is_imidj' => true])->first()->getUrl('medium');
    }
}
