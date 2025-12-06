<?php

namespace App\Models\Media;

use App\Models\Banner;
use App\Models\Feedback;
use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class PathGenerator extends DefaultPathGenerator
{
    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media, bool $new = false): string
    {
        // todo: remove after move to S3
        if (!$new) {
            return $this->getOldBasePath($media);
        }

        $path = match ($media->model_type) {
            Banner::class => 'b',
            Feedback::class => 'f',
            Product::class => 'p',
            default => 'other',
        };

        if ($media->model_type === Product::class) {
            $path .= '/' . (substr($media->model_id, 0, -3) ?: '0') . '/' . $media->model_id;
        }

        return "$path/{$media->getKey()}";
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        // todo: remove after move to S3
        if ($media->conversions_disk === 'public') {
            return parent::getPathForConversions($media);
        }

        return $this->getBasePath($media, $media->hasCustomProperty('moving')) . '-';
    }

    private function getOldBasePath(Media $media): string
    {
        $path = 'other';
        $nestingLevel = 0;
        $key = $media->getKey();

        switch ($media->model_type) {
            case Banner::class:
                $path = 'b'; // banners
                break;

            case Feedback::class:
                $path = 'feedbacks';
                break;

            case Product::class:
                $nestingLevel = 4;
                $path = 'products';
                break;
        }

        for ($i = 0; $i < $nestingLevel; $i++) {
            $path .= '/' . substr($key, 0, $i + 1);
        }

        return "$path/$key";
    }
}
