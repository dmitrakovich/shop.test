<?php

namespace App\Models;

use App\Models\ProductAttributes\Status;
use Illuminate\Support\Facades\Cache;

class Filter
{
    /**
     * связанные с фильтрами классы
     *
     * @var array
     */
    protected static $filtersModels = [
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
     * Prefixes for selected filters
     */
    protected static array $filtersPrefixes = [
        Size::class => 'Размер: ',
    ];

    /**
     * Получить все фильтра
     *
     * @param  array  $filtersList список нужных фильтров
     * @return array
     */
    public static function all(array $filtersList = null)
    {
        if (Cache::has('filters')) {
            $filters = Cache::get('filters');
        }

        if (! isset($filters)) {
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
     * Prefix for the display name of the selected filter
     */
    public static function getNamePrefix(string $filterModel): string
    {
        return self::$filtersPrefixes[$filterModel] ?? '';
    }
}
