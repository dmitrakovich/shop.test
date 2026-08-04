<?php

namespace Tests\Unit\Elastic;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributes\Status;
use App\Models\Url;
use App\Services\Elasticsearch\CatalogFacetService;
use App\Services\Elasticsearch\CatalogSearchResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFacetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_only_available_minimal_facet_values(): void
    {
        $brand = Brand::query()->firstOrFail();
        $status = Status::query()->where('slug', 'st-new')->firstOrFail();
        $result = new CatalogSearchResult(
            productIds: [],
            total: 0,
            minPrice: 0,
            maxPrice: 0,
            facetCounts: [
                'brands' => [(string)$brand->id => 5],
                'statuses' => ['st-new' => 2],
            ],
        );

        $facets = (new CatalogFacetService())->build(
            [
                Brand::class => [$brand->slug => new Url()],
                Status::class => [$status->slug => new Url()],
            ],
            $result,
        );

        $this->assertSame([[
            'id' => $brand->id,
            'slug' => $brand->slug,
            'name' => $brand->name,
            'count' => 5,
            'selected' => true,
        ]], $facets['brands']);
        $this->assertSame([[
            'id' => $status->id,
            'slug' => 'st-new',
            'name' => $status->name,
            'count' => 2,
            'selected' => true,
        ]], $facets['statuses']);
    }

    public function test_it_builds_sparse_category_tree(): void
    {
        $selected = Category::query()
            ->whereNotNull('parent_id')
            ->firstOrFail();
        $selected->loadParentCategoryChain();
        $chain = collect([$selected]);
        $parent = $selected->parentCategory;
        while ($parent !== null) {
            $chain->prepend($parent);
            $parent = $parent->parentCategory;
        }
        $counts = $chain->mapWithKeys(
            static fn (Category $category): array => [(string)$category->id => 4],
        )->all();

        $result = new CatalogSearchResult(
            productIds: [],
            total: 0,
            minPrice: 0,
            maxPrice: 0,
            facetCounts: ['categories' => $counts],
        );

        $facets = (new CatalogFacetService())->build(
            [Category::class => [$selected->slug => new Url()]],
            $result,
        );

        $this->assertSame(4, $facets['categories'][0]['count']);
        $this->assertSame(
            ['id', 'slug', 'path', 'title', 'count', 'selected', 'children'],
            array_keys($facets['categories'][0]),
        );
        $this->assertTrue($this->findCategory($facets['categories'], $selected->id)['selected']);
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @return array<string, mixed>
     */
    private function findCategory(array $categories, int $id): array
    {
        foreach ($categories as $category) {
            if ($category['id'] === $id) {
                return $category;
            }

            $match = $this->findCategory($category['children'], $id);
            if ($match !== []) {
                return $match;
            }
        }

        return [];
    }
}
