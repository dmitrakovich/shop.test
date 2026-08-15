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

        foreach (CatalogFacetName::cases() as $facet) {
            $name = $facet->value;
            $counts = $result->facetCounts[$name] ?? [];
            if ($counts === []) {
                continue;
            }

            $model = $facet->model();
            $selectedSlugs = array_keys($currentFilters[$model] ?? []);
            $facets[$name] = $facet === CatalogFacetName::Categories
                ? $this->categories($counts, $selectedSlugs)
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
        $model = $facet->model();
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
                $slug = (string)$record->getAttribute('slug');
                $facetValue = [
                    'id' => (int)$record->getAttribute('id'),
                    'slug' => $slug,
                    'name' => (string)$record->getAttribute('name'),
                    'count' => $counts[(string)$record->getAttribute($key)],
                    'selected' => in_array($slug, $selectedSlugs, true),
                ];

                foreach ($extras as $extra) {
                    $facetValue[$extra] = $record->getAttribute($extra);
                }

                return $facetValue;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @param  list<string>  $selectedSlugs
     * @return list<array<string, mixed>>
     */
    private function categories(array $counts, array $selectedSlugs): array
    {
        /** @var EloquentCollection<int, Category> $categories */
        $categories = Category::query()
            ->whereIn('id', array_keys($counts))
            ->get(Category::elasticFacetColumns());

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
