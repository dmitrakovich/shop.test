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
        'fabrics',
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
        'fabrics' => 'App\Models\Fabric',
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
        $filtersList = $filtersList ?? self::$filtersList;
        // Cache::tags(['filters'])->flush();
        foreach ($filtersList as $filterName) {
            $model = self::$filtersModels[$filterName];
            $filters[$filterName] = 
                // Cache::tags(['filters'])
                // ->rememberForever("filters.$filterName", function () use ($model) {
                    // $query = $model::with('slug');
                    // if ($model == 'App\Category') {
                    //     $query->where('parent_id', 0)->with('childrenCategories')->orderBy('sorting');
                    // }
                    // return 
                    $model::get()->keyBy('id')->toArray();
                // });
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
