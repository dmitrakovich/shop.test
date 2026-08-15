<?php

namespace App\Services;

use App\Models\ProductAttributes\Price;
use App\Models\Url;

class FilterService
{
    /**
     * Generate static filter
     */
    public function getStaticFilter(string $slug): ?Url
    {
        if (str_starts_with($slug, 'price-')) {
            return $this->makeUrlFilter(new Price(['slug' => $slug]));
        }

        return null;
    }

    /**
     * Add filter to Url model
     */
    public function makeUrlFilter($filter): Url
    {
        $urlModel = new Url([
            'slug' => $filter->slug,
            'model_type' => $filter::class,
            'model_id' => $filter->id,
        ]);

        return $urlModel->setRelation('filters', $filter);
    }
}
