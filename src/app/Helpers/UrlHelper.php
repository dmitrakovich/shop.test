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
    Style,
    Category,
    Collection
};
use App\Models\ProductAttributes\Status;
use Illuminate\Support\Facades\Request;

class UrlHelper
{
    /**
     * Order of filters in an url string
     */
    const CANONICAL_ORDER = [
        Category::class,
        Status::class,
        Size::class,
        Color::class,
        Fabric::class,
        Heel::class,
        Tag::class,
        Season::class,
        Style::class,
        Collection::class,
        Brand::class,
    ];

    /**
     * At most one value
     */
    const UNIQUE_FILTERS = [
        Category::class,
    ];

    protected static $params = null;
    protected static $availableParams = [
        'search'
    ];
    protected static $currentFilters = [];

    /**
     * Generate url for filter
     */
    public static function generate(array $add = [], array $remove = [])
    {
        $filters = self::$currentFilters;
        $params = self::getParams();

        foreach ($add as $filter) {
            $model = $filter['model'];
            $slug = $filter['slug'];
            if (in_array($model, self::UNIQUE_FILTERS)) {
                $filters[$model] = [$slug => $filter];
            } else {
                $filters[$model][$slug] = $filter;
            }
        }

        foreach ($remove as $filter) {
            if (isset($filter['param'])) {
                unset($params[$filter['param']]);
            } else {
                unset($filters[$filter['model']][$filter['slug']]);
            }
        }

        $sorted = [];
        foreach (self::CANONICAL_ORDER as $model) {
            if (isset($filters[$model])) {
                if ($model == Category::class) {
                      $filter = end($filters[$model]);
                  if($filter instanceof Category) {
                      $sorted[] = $filter->path;
                  } else {
                      $filterPath = $filter['filters']['path'] ?? '';
                      if($filterPath != 'catalog') {
                          $filterCatPath = substr($filterPath, strrpos($filterPath, "/")+ 1);
                          $sorted[] = $filterCatPath;
                      }
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
