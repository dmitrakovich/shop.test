<?php

namespace App\Models;

use App\Models\{
    Tag,
    Heel,
    Size,
    Brand,
    Color,
    Fabric,
    Season,
    Category,
    Collection
};
use Illuminate\Support\Facades\Cache;
use App\Models\ProductAttributes\Status;

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
        'tags' => Tag::class,
        'brands' => Brand::class,
    ];

    /**
     * префиксы для выбранных фильтров
     *
     * @var array
     */
    protected static $filtersPrefixes = [
        'App\Size' => 'Размер ',
        'App\Height' => 'Рост ',
    ];

    /**
     * Получить все фильтра
     *
     * @param array $filtersList список нужных фильтров
     * @return array
     */
    public static function all(array $filtersList = null)
    {
        if (Cache::has('filters')) {
            $filters = Cache::get('filters');
        }

        if (!isset($filters)) {
            $filtersList = $filtersList ?? array_keys(self::$filtersModels);
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
     * Префикс для отображаемого названия выбранного фильтра
     *
     * @param string $filterType
     * @return string
     */
    public static function getNamePrefix(string $filterType)
    {
        return self::$filtersPrefixes[$filterType] ?? '';
    }
}
