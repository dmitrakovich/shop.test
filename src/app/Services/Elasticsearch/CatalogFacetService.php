<?php

namespace App\Services\Elasticsearch;

use App\Enums\Catalog\CatalogFacetName;
use App\Models\Category;
use App\Models\Url;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class CatalogFacetService
{
    /**
     * @param  array<string, array<string, Url>>  $currentFilters
     * @return array<string, list<array<string, mixed>>>
     */
    public function build(array $currentFilters, CatalogSearchResult $result): array
    {
        $facets = [];

        foreach (CatalogFacetDefinition::all() as $config) {
            $name = $config->name->value;
            $counts = $result->facetCounts[$name] ?? [];
            if ($counts === []) {
                continue;
            }

            $selectedSlugs = array_keys($currentFilters[$config->model] ?? []);
            $facets[$name] = $config->name === CatalogFacetName::Categories
                ? $this->categories($config, $counts, $selectedSlugs)
                : $this->values($config, $counts, $selectedSlugs);

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
    private function values(CatalogFacetConfig $config, array $counts, array $selectedSlugs): array
    {
        $model = $config->model;
        $query = $model::query()
            ->select($config->columns)
            ->whereIn($config->key, array_keys($counts));

        foreach ($config->where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get()
            ->map(function (Model $model) use ($config, $counts, $selectedSlugs): array {
                $facet = [
                    'id' => (int)$model->getAttribute('id'),
                    'slug' => (string)$model->getAttribute('slug'),
                    'name' => (string)$model->getAttribute('name'),
                    'count' => $counts[(string)$model->getAttribute($config->key)],
                    'selected' => in_array((string)$model->getAttribute('slug'), $selectedSlugs, true),
                ];

                foreach ($config->extras as $extra) {
                    $facet[$extra] = $model->getAttribute($extra);
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
    private function categories(CatalogFacetConfig $config, array $counts, array $selectedSlugs): array
    {
        /** @var EloquentCollection<int, Category> $categories */
        $categories = Category::query()
            ->whereIn('id', array_keys($counts))
            ->get($config->columns);

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
}
