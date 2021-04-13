<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

class UrlHelper
{
    protected static $canonicalOrder = [
        # model => св-ва [уникальный, ]
        // 1. prstatus (новинка, на распродаже или акция)
        'App\Models\Category' => [true,],          // 2. Категория
        'App\Models\Size' => [false,],             // 3. Размер
        'App\Models\Color' => [false,],            // 4. Цвет
        'App\Models\Fabric' => [false,],           // 5. Материал
        'App\Models\Style' => [false,],            // 6. Стиль
        'App\Models\Heel' => [false,],             // 7. Каблук
        'App\Models\Tag' => [false,],              // 8. Теги
        'App\Models\Season' => [false,],           // 9. Сезон
        'App\Models\Collection' => [false,],       // 10. Коллекция
        // 11. Город
        'App\Models\Brand' => [false,],            // 12. Бренд
    ];
    protected static $params = null;
    protected static $availableParams = [
        'search'
    ];

    protected static $currentFilters = [];

    public static function generate(array $add = [], array $remove = [])
    {
        $filters = self::$currentFilters;
        $params = self::getParams();

        foreach ($add as $filter) {
            $filters[$filter['model']][$filter['slug']] = $filter;
        }

        foreach ($remove as $filter) {
            if (isset($filter['param'])) {
                unset($params[$filter['param']]);
            } else {
                unset($filters[$filter['model']][$filter['slug']]);
            }
        }

        $sorted = [];
        foreach (self::$canonicalOrder as $model => list($single)) {
            if (isset($filters[$model])) {
                if ($model == 'App\Models\Category') {
                    array_unshift($sorted, end($filters[$model])['filters']['path']);
                } elseif ($single) {
                    if (!empty(end($filters[$model]))) {
                        $sorted[] = end($filters[$model])['slug'];
                    }
                } else {
                    sort($filters[$model]);
                    foreach ($filters[$model] as $filter) {
                        $sorted[] = $filter['slug'];
                    }
                }
            }
        }
        return route('shop', implode('/', $sorted) . self::buildParams($params));
    }
    /**
     * Получить параметры из запроса
     *
     * @return array
     */
    protected static function getParams(): array
    {
        if (is_null(self::$params)) {
            self::$params = [];
            foreach (Request::input() as $key => $value) {
                if (in_array($key, self::$availableParams)) {
                    self::$params[$key] = $value;
                }
            }
        }
        return self::$params;
    }
    /**
     * Построить Url кодированный запрос из параметров
     *
     * @param array $params
     * @return string|null
     */
    protected static function buildParams(array $params): ?string
    {
        return empty($params) ? null : '?' . http_build_query($params);
    }

    public static function setCurrentFilters(array $currentFilters)
    {
        self::$currentFilters = $currentFilters;
        // dd($currentFilters);
        /*foreach ($currentFilters as $model => $items) {
            foreach ($items as $item) {
                self::$currentFilters[$model][$item['slug']['slug']] = $item['slug']['slug'];
            }
        }*/
    }

    public static function getEmbedVideoUrl(string $originalVideoUrl, $extPrams = [])
    {
        parse_str(parse_url($originalVideoUrl, PHP_URL_QUERY), $params);

        return 'https://www.youtube.com/embed/' . $params['v']; //  . http_build_query($extPrams); // ?autoplay=1&rel=0
    }
}
