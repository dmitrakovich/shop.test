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
    protected function getBasePath(Media $media): string
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
            $path .= '/'.substr($key, 0, $i + 1);
        }

        return "$path/$key";
    }
}
