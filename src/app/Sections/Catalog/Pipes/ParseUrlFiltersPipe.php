<?php

namespace App\Sections\Catalog\Pipes;

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
use App\Services\FilterService;
use Closure;

/**
 * Pipe: resolve slug path filters via Url model into Elasticsearch filter keys.
 */
class ParseUrlFiltersPipe
{
    /** @var array<class-string, string> Model type → ES filter key */
    private const MODEL_FILTER_KEYS = [
        Category::class => 'categories.id',
        Brand::class => 'brand.id',
        Size::class => 'sizes.id',
        Color::class => 'colors.id',
        Fabric::class => 'fabrics.id',
        Heel::class => 'heels.id',
        Season::class => 'seasons.id',
        Style::class => 'styles.id',
        Tag::class => 'tags.id',
        Collection::class => 'collection.id',
        Status::class => 'statuses',
    ];

    /**
     * @param  FilterService  $filterService  Static filter helper
     */
    public function __construct(
        private readonly FilterService $filterService,
    ) {}

    /**
     * Resolve URL slugs into activeFilters and urlFilters.
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        /** @var \App\Data\Catalog\FilterStateData $state */
        $state = $passable['state'];
        /** @var list<string> $segments */
        $segments = $passable['segments'] ?? [];

        if ($segments === []) {
            return $next($passable);
        }

        $slugs = $segments;
        $urlFilters = [];

        foreach ($slugs as $key => $slug) {
            if ($url = $this->filterService->getStaticFilter($slug)) {
                $urlFilters[$url->model_type][$url->slug] = $url;
                unset($slugs[$key]);
            }
        }

        if ($slugs !== []) {
            Url::query()
                ->whereIn('slug', $slugs)
                ->with('filters')
                ->get(['slug', 'model_type', 'model_id'])
                ->each(function (Url $url) use (&$urlFilters): void {
                    $urlFilters[$url->model_type][$url->slug] = $url;
                });
        }

        $state->urlFilters = $urlFilters;

        foreach ($urlFilters as $modelType => $group) {
            if ($modelType === Price::class) {
                $this->applyPriceFilters($state, $group);

                continue;
            }

            $filterKey = self::MODEL_FILTER_KEYS[$modelType] ?? null;
            if ($filterKey === null) {
                continue;
            }

            foreach ($group as $url) {
                /** @var Url $url */
                if ($modelType === Status::class) {
                    $state->activeFilters[$filterKey][] = (string)$url->slug;
                    $state->activeOptions = true;

                    continue;
                }

                if ($modelType === Category::class) {
                    $category = $url->filters;
                    if (!$category instanceof Category) {
                        continue;
                    }

                    if ((int)$category->id === Category::ROOT_CATEGORY_ID) {
                        continue;
                    }

                    $ids = Category::getChildrenCategoriesIdsList((int)$category->id);

                    $state->activeFilters[$filterKey] = array_values(array_unique(array_merge(
                        $state->activeFilters[$filterKey] ?? [],
                        $ids
                    )));

                    continue;
                }

                $state->activeFilters[$filterKey][] = (int)$url->model_id;
                $state->activeOptions = true;
            }
        }

        foreach ($state->activeFilters as $key => $values) {
            if (is_array($values)) {
                $state->activeFilters[$key] = array_values(array_unique($values));
            }
        }

        $passable['state'] = $state;

        return $next($passable);
    }

    /**
     * Apply price-from / price-to slugs to state.
     *
     * @param  \App\Data\Catalog\FilterStateData  $state  Filter state
     * @param  array<string, Url>  $group  Price Url filters
     */
    private function applyPriceFilters(object $state, array $group): void
    {
        foreach ($group as $url) {
            $price = $url->filters;
            if (!$price instanceof Price) {
                continue;
            }

            $value = (float)$price->price;
            if (str_starts_with((string)$price->slug, 'price-from-')) {
                $state->priceMin = $value;
            } elseif (str_starts_with((string)$price->slug, 'price-to-')) {
                $state->priceMax = $value;
            }
        }
    }
}
