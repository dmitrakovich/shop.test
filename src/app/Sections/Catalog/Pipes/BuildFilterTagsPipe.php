<?php

namespace App\Sections\Catalog\Pipes;

use App\Contracts\Filterable;
use App\Helpers\UrlHelper;
use App\Models\Category;
use App\Models\Url;
use Closure;

/**
 * Pipe: build filter tags (name + remove URL) from urlFilters.
 */
class BuildFilterTagsPipe
{
    /**
     * Build UI filter tags from resolved Url filters.
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        /** @var \App\Data\Catalog\FilterStateData $state */
        $state = $passable['state'];
        $tags = [];

        UrlHelper::setCurrentFilters($state->urlFilters);

        foreach ($state->urlFilters as $modelType => $group) {
            $items = $group;
            if ($modelType === Category::class && $items !== []) {
                $items = [end($items)];
            }

            foreach ($items as $url) {
                if (!$url instanceof Url) {
                    continue;
                }

                $filter = $url->filters;
                if (!$filter instanceof Filterable || $filter->isInvisible()) {
                    continue;
                }

                $tags[] = [
                    'name' => $filter->getBadgeName(),
                    'slug' => (string)$url->slug,
                    'route' => UrlHelper::generate([], [$filter]),
                ];
            }
        }

        if ($state->search !== null && $state->search !== '') {
            $tags[] = [
                'name' => 'Поиск: '.mb_strimwidth($state->search, 0, 12, '...'),
                'slug' => '',
                'route' => UrlHelper::generate([], [['param' => 'search']]),
            ];
        }

        $state->filterTags = $tags;
        $passable['state'] = $state;

        return $next($passable);
    }
}
