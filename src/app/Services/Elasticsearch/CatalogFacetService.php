<?php

namespace App\Services\Elasticsearch;

use App\Contracts\Filterable;
use App\Enums\Catalog\CatalogFacetName;
use App\Models\Category;
use App\Models\Url;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LogicException;

final class CatalogFacetService
{
    /**
     * @param  array<string, array<string, Url>>  $currentFilters
     * @return array<string, list<array<string, mixed>>>
     */
    public function build(array $currentFilters, CatalogSearchResult $result): array
    {
        $facets = [];

        foreach (CatalogFacetName::cases() as $facet) {
            $name = $facet->value;
            $counts = $result->facetCounts[$name] ?? [];
            if ($counts === []) {
                continue;
            }

            $model = $this->filterableModel($facet);
            $selectedSlugs = array_keys($currentFilters[$model] ?? []);
            $facets[$name] = $facet === CatalogFacetName::Categories
                ? $this->categories($facet, $counts, $selectedSlugs)
                : $this->values($facet, $counts, $selectedSlugs);

            if ($facets[$name] === []) {
                unset($facets[$name]);
            }
        }

        return $facets;
    }

    /**
     * @param  array<string, int>  $counts
     * @param  list<string>  $selectedSlugs
     * @return list<array<string, mixed>>
     */
    private function values(CatalogFacetName $facet, array $counts, array $selectedSlugs): array
    {
        $model = $this->filterableModel($facet);
        $key = $model::elasticFacetKey();
        $extras = $model::elasticFacetExtras();
        $query = $model::query()
            ->select($model::elasticFacetColumns())
            ->whereIn($key, array_keys($counts));

        foreach ($model::elasticFacetWhere() as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get()
            ->map(function (Model $record) use ($key, $extras, $counts, $selectedSlugs): array {
                $facet = [
                    'id' => (int)$record->getAttribute('id'),
                    'slug' => (string)$record->getAttribute('slug'),
                    'name' => (string)$record->getAttribute('name'),
                    'count' => $counts[(string)$record->getAttribute($key)],
                    'selected' => in_array((string)$record->getAttribute('slug'), $selectedSlugs, true),
                ];

                foreach ($extras as $extra) {
                    $facet[$extra] = $record->getAttribute($extra);
                }

                return $facet;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @param  list<string>  $selectedSlugs
     * @return list<array<string, mixed>>
     */
    private function categories(CatalogFacetName $facet, array $counts, array $selectedSlugs): array
    {
        $model = $this->filterableModel($facet);

        /** @var EloquentCollection<int, Category> $categories */
        $categories = Category::query()
            ->whereIn('id', array_keys($counts))
            ->get($model::elasticFacetColumns());

        return $this->categoryChildren(
            collect($categories->groupBy('parent_id')->all()),
            null,
            $counts,
            $selectedSlugs,
        );
    }

    /**
     * @param  Collection<int|string, EloquentCollection<int, Category>>  $categoriesByParent
     * @param  array<string, int>  $counts
     * @param  list<string>  $selectedSlugs
     * @return list<array<string, mixed>>
     */
    private function categoryChildren(
        Collection $categoriesByParent,
        ?int $parentId,
        array $counts,
        array $selectedSlugs,
    ): array {
        return $categoriesByParent->get($parentId, new EloquentCollection())
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'slug' => $category->slug,
                'path' => $category->path,
                'title' => $category->title,
                'count' => $counts[(string)$category->id],
                'selected' => in_array($category->slug, $selectedSlugs, true),
                'children' => $this->categoryChildren(
                    $categoriesByParent,
                    $category->id,
                    $counts,
                    $selectedSlugs,
                ),
            ])
            ->values()
            ->all();
    }

    /**
     * @return class-string
     */
    private function filterableModel(CatalogFacetName $facet): string
    {
        $model = $facet->model();
        if (!is_a($model, Filterable::class, true)) {
            throw new LogicException("Facet [{$facet->value}] model [{$model}] must implement Filterable.");
        }

        return $model;
    }
}
