<?php

namespace App\Services;

use App\Facades\Currency;
use App\Models\Brand;
use App\Models\Category;
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
use App\Models\Url;
use Illuminate\Support\Facades\Cache;

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
     * @param  array  $filtersList  список нужных фильтров
     */
    public static function getAll(?array $filtersList = null): array
    {
        if (!$filters = Cache::get('filters')) {
            $filtersList ??= array_keys(self::$filtersModels);
            foreach ($filtersList as $filterName) {
                $model = self::$filtersModels[$filterName];
                $query = (new $model())->newQuery();
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
        if (str_starts_with($slug, 'price-')) {
            return $this->makeUrlFilter(new Price(['slug' => $slug]));
        }

        return null;
    }

    /**
     * Add filter to Url model
     */
    public function makeUrlFilter($filter): Url
    {
        $urlModel = new Url([
            'slug' => $filter->slug,
            'model_type' => get_class($filter),
            'model_id' => $filter->id,
        ]);

        return $urlModel->setRelation('filters', $filter);
    }

    /**
     * Make price filter models for filters slugs
     */
    public function makePriceFilters(array $data): array
    {
        $filters = [];
        if ($data['price_from'] > $data['price_min']) {
            $slug = 'price-from-' . Currency::reverseConvert($data['price_from']);
            $filters[] = new Price(['slug' => $slug]);
        }
        if ($data['price_to'] < $data['price_max']) {
            $slug = 'price-to-' . Currency::reverseConvert($data['price_to']);
            $filters[] = new Price(['slug' => $slug]);
        }

        return $filters;
    }
}
