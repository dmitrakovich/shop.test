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
        $path = match ($media->model_type) {
            Banner::class => 'b',
            Feedback::class => 'f',
            Product::class => 'p',
            default => 'other',
        };

        if ($media->model_type === Product::class) {
            $path .= '/' . substr($media->model_id, 0, -3) . '/' . $media->model_id;
        }

        return "$path/{$media->getKey()}";
    }
}
