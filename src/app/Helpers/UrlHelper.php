<?php

namespace App\Helpers;

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
use App\Models\ProductAttributes\Status;
use Illuminate\Support\Facades\Request;

class UrlHelper
{
    protected static $canonicalOrder = [
        # model => св-ва [уникальный, ]
        Category::class => [true,],                // 1. Категория
        Status::class => [false,],                 // 2. Статус
        Size::class => [false,],                   // 3. Размер
        Color::class => [false,],                  // 4. Цвет
        Fabric::class => [false,],                 // 5. Материал
        Style::class => [false,],                  // 6. Стиль
        Heel::class => [false,],                   // 7. Каблук
        Tag::class => [false,],                    // 8. Теги
        Season::class => [false,],                 // 9. Сезон
        Collection::class => [false,],             // 10. Коллекция
        // 11. Город
        Brand::class => [false,],                  // 12. Бренд
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
        foreach (self::$canonicalOrder as $model => [$single]) {
            if (isset($filters[$model])) {
                if ($model == Category::class) {
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

        return route('shop', str_replace('catalog/', '', implode('/', $sorted)) . self::buildParams($params));
    }

    /**
     * Получить параметры из запроса
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

        return 'https://www.youtube.com/embed/' . ($params['v'] ?? 'hrwJvG8kALA'); //  . http_build_query($extPrams); // ?autoplay=1&rel=0
    }
}
