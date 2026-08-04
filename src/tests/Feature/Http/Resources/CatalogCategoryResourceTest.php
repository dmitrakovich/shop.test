<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\Product\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_minimal_category_with_parent_chain(): void
    {
        $category = Category::query()
            ->whereNotNull('parent_id')
            ->firstOrFail();
        $category->loadParentCategoryChain();

        $this->assertSame(
            $this->expectedCategory($category),
            (new CategoryResource($category))->response()->getData(true),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function expectedCategory(?Category $category): ?array
    {
        if ($category === null) {
            return null;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'path' => $category->path,
            'parent_category' => $this->expectedCategory($category->parentCategory),
        ];
    }
}
