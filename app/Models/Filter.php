<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Filter
{
    /**
     * Список фильтров
     *
     * @var array
     */
    protected static $filtersList = [
        'categories',
        'fabrics',
        'collections',
        'sizes',
        'colors',
        'heels',
        'seasons',
        'tags',
        'brands',
    ];
    /**
     * связанные с фильтрами классы
     *
     * @var array
     */
    protected static $filtersModels = [
        'categories' => 'App\Models\Category',
        'fabrics' => 'App\Models\Fabric',
        'collections' => 'App\Models\Collection',
        'sizes' => 'App\Models\Size',
        'colors' => 'App\Models\Color',
        'heels' => 'App\Models\Heel',
        'seasons' => 'App\Models\Season',
        'tags' => 'App\Models\Tag',
        'brands' => 'App\Models\Brand',
    ];
    /**
     * префиксы для выьранных фильтров
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
            $filtersList = $filtersList ?? self::$filtersList;
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
