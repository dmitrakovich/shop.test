<?php

namespace App\Services;

use App\Contracts\Filterable;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FilterService
{
    /**
     * Filter-related classes
     *
     * @var class-string<Model&Filterable>[]
     */
    private const array FILTERS_MODELS = [
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
     */
    public function getAll(): array
    {
        return Cache::remember('filters', now()->addDay(), function () {
            foreach (self::FILTERS_MODELS as $filterName => $model) {
                $filters[$filterName] = $model::getFilters();
            }

            return array_filter($filters);
        });
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
            $slug = 'price-from-' . Currency::reverseConvert((float)$data['price_from']);
            $filters[] = new Price(['slug' => $slug]);
        }
        if ($data['price_to'] < $data['price_max']) {
            $slug = 'price-to-' . Currency::reverseConvert((float)$data['price_to']);
            $filters[] = new Price(['slug' => $slug]);
        }

        return $filters;
    }
}
