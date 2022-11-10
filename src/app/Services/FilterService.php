<?php

namespace App\Services;

use App\Models\{
    Tag,
    Url,
    Heel,
    Size,
    Brand,
    Color,
    Style,
    Fabric,
    Season,
    Category,
    Collection,
};
use Illuminate\Support\Facades\Cache;
use App\Models\ProductAttributes\Status;

class FilterService
{
    /**
     * Filter-related classes
     */
    protected static array $filtersModels = [
        'categories' => Category::class,
        'statuses' => Status::class,
        'fabrics' => Fabric::class,
        'collections' => Collection::class,
        'sizes' => Size::class,
        'colors' => Color::class,
        'heels' => Heel::class,
        'seasons' => Season::class,
        'styles' => Style::class,
        'tags' => Tag::class,
        'brands' => Brand::class,
    ];

    /**
     * Получить все фильтра
     *
     * @param array $filtersList список нужных фильтров
     * @return array
     */
    public static function getAll(array $filtersList = null)
    {
        if (Cache::has('filters')) {
            $filters = Cache::get('filters');
        }

        if (!isset($filters)) {
            $filtersList ??= array_keys(self::$filtersModels);
            foreach ($filtersList as $filterName) {
                $model = self::$filtersModels[$filterName];
                $query = (new $model)->newQuery();
                if ($filterName == 'categories') {
                    $filters[$filterName] = $query->whereNull('parent_id')
                        ->with('childrenCategories')->get(); // говнокод;
                } else {
                    $filters[$filterName] = $query->get()->keyBy('slug')->toArray();
                }
                foreach ($filters[$filterName] as &$value) {
                    $value['model'] = $model;
                }
            }
            Cache::put('filters', $filters, 86400); // day
        }
        return $filters;
    }

    /**
     * Generate static filter
     */
    public function getStaticFilter(string $slug): ?Url
    {
        if (str_starts_with($slug, 'price-from-')) {
            // Price
        }

        // return match ($slug) {
        //     'size-40' =>
        //     default => null,
        // };
        // if ($slug === ) {
        //     return Url::where('slug', 'size-40')->with('filters')->first();
        // }

        return null;
    }
}
