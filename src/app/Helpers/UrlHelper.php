<?php

namespace App\Helpers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Price;
use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;
use Illuminate\Support\Facades\Request;

class UrlHelper
{
    /**
     * Order of filters in an url string
     */
    const CANONICAL_ORDER = [
        Category::class,
        Brand::class,
        Status::class,
        Color::class,
        Fabric::class,
        Size::class,
        Heel::class,
        Tag::class,
        Season::class,
        Style::class,
        Collection::class,
        Price::class,
    ];

    /**
     * At most one value
     */
    const UNIQUE_FILTERS = [
        Category::class,
    ];

    protected static $params = null;

    protected static $availableParams = [
        'search',
    ];

    protected static $currentFilters = [];

    protected static ?City $currentCity = null;

    /**
     * Generate url for filter
     */
    public static function generate(array $add = [], array $remove = [], $canonical = false)
    {
        $filters = self::$currentFilters;
        $city = self::$currentCity;
        $params = self::getParams();

        foreach ($remove as $filter) {
            if (isset($filter['param'])) {
                unset($params[$filter['param']]);
            } else {
                if (in_array($filter['model'], [Category::class, Price::class])) {
                    unset($filters[$filter['model']]);
                } else {
                    unset($filters[$filter['model']][$filter['slug']]);
                }
            }
        }

        foreach ($add as $filter) {
            $model = $filter['model'];
            $slug = $filter['slug'];
            if (in_array($model, self::UNIQUE_FILTERS)) {
                $filters[$model] = [$slug => $filter];
            } else {
                $filters[$model][$slug] = $filter;
            }
            if ($model === Category::class) {
                $params = [];
            }
        }

        $sorted = [];
        foreach (self::CANONICAL_ORDER as $model) {
            if (isset($filters[$model])) {
                if ($model == Category::class) {
                    $filter = end($filters[$model]);
                    $sorted[] = ($filter instanceof Category) ? ($filter->path ?? '') : ($filter['filters']['path'] ?? '');
                } else {
                    sort($filters[$model]);
                    foreach ($filters[$model] as $filter) {
                        if ($model === Price::class && $canonical) {
                            $priceVal = $filter->filters->getPriceAttribute();
                            if (!str_contains($filter['slug'], 'price-from') && !($priceVal % 50)) {
                                $sorted[] = $filter['slug'];
                            }
                        } else {
                            $sorted[] = $filter['slug'];
                        }
                    }
                }
            }
        }

        if ($city) {
            array_unshift($sorted, 'city-' . $city->slug);
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
    }

    /**
     * Установить текущий город
     */
    public static function setCurrentCity(?City $city): void
    {
        self::$currentCity = $city;
    }

    public static function getEmbedVideoUrl(string $originalVideoUrl, $extPrams = [])
    {
        parse_str(parse_url($originalVideoUrl, PHP_URL_QUERY), $params);

        return 'https://www.youtube.com/embed/' . ($params['v'] ?? 'hrwJvG8kALA'); //  . http_build_query($extPrams); // ?autoplay=1&rel=0
    }
}
