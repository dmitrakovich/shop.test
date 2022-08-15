<?php

namespace App\Services\Seo;

use App\Models\Category;

class MetaService
{
    const MAX_FILTERS_COUNT = 3;
    const MAX_FILTER_VALUES_COUNT = 1;

    /**
     * Prepare meta info for robots
     */
    public function metaForRobotsForCatalog(array $currentFilters): string
    {
        $filtersCount = 0;
        foreach ($currentFilters as $filterType => $filters) {
            $filterValuesCount = intval($filterType === Category::class) ?: count($filters);
            $filtersCount += $filterValuesCount;

            if ($filtersCount > self::MAX_FILTERS_COUNT || $filterValuesCount > self::MAX_FILTER_VALUES_COUNT) {
                return 'noindex, nofollow';
            }
        }

        return 'all';
    }
}
