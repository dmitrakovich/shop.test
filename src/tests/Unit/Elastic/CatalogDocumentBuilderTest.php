<?php

namespace Tests\Unit\Elastic;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Tag;
use App\Services\Elasticsearch\CatalogDocumentBuilder;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CatalogDocumentBuilderTest extends TestCase
{
    public function test_it_builds_filter_sort_and_search_fields(): void
    {
        $category = $this->makeCategory(10, 'Кроссовки');
        $brand = $this->makeBrand(5, 'Nike Test');
        $size = $this->makeSizedModel(Size::class, 101, '38');
        $color = $this->makeSizedModel(Color::class, 202, 'бордовый');
        $tag = $this->makeTag(303, 'вечерние');

        $product = $this->makeProduct([
            'id' => 42,
            'sku' => 'SKU-ES-100',
            'slug' => 'sku-es-100',
            'price' => 100.50,
            'old_price' => 150.00,
            'category_id' => 10,
            'brand_id' => 5,
            'season_id' => 1,
            'collection_id' => 2,
            'color_txt' => 'черный',
            'rating' => 10,
            'newness_rating' => 20,
            'season_rating' => 30,
            'sale_rating' => 40,
        ], [
            'category' => $category,
            'brand' => $brand,
            'sizes' => collect([$size]),
            'colors' => collect([$color]),
            'tags' => collect([$tag]),
        ]);

        $document = (new CatalogDocumentBuilder)->build($product);

        $this->assertSame(42, $document['id']);
        $this->assertSame('SKU-ES-100', $document['sku']);
        $this->assertTrue($document['has_discount']);
        $this->assertFalse($document['is_new']);
        $this->assertSame(['st-sale'], $document['status_slugs']);
        $this->assertSame(10, $document['rating']);
        $this->assertSame(['id' => 5, 'name' => 'Nike Test'], $document['brand']);
        $this->assertSame('Кроссовки 42', $document['short_name']);
        $this->assertSame([['id' => 10, 'name' => 'Кроссовки']], $document['categories']);
        $this->assertSame('черный', $document['color_txt']);
        $this->assertSame([['id' => 202, 'name' => 'бордовый']], $document['colors']);
        $this->assertSame([['id' => 303, 'name' => 'вечерние']], $document['tags']);
        $this->assertSame([['id' => 101, 'name' => '38']], $document['sizes']);
        $this->assertArrayNotHasKey('brand_id', $document);
        $this->assertArrayNotHasKey('color_ids', $document);
    }

    public function test_it_marks_new_products_without_old_price(): void
    {
        $product = $this->makeProduct([
            'id' => 7,
            'sku' => 'NEW-1',
            'slug' => 'new-1',
            'price' => 99,
            'old_price' => 0,
        ]);

        $document = (new CatalogDocumentBuilder())->build($product);

        $this->assertTrue($document['is_new']);
        $this->assertFalse($document['has_discount']);
        $this->assertSame(['st-new'], $document['status_slugs']);
    }

    public function test_categories_include_ancestor_chain(): void
    {
        $root = $this->makeCategory(1, 'Root');
        $child = $this->makeCategory(2, 'Child', $root);

        $product = $this->makeProduct([
            'id' => 9,
            'sku' => 'X',
            'slug' => 'x',
            'price' => 1,
            'old_price' => 0,
            'category_id' => 2,
        ], [
            'category' => $child,
        ]);

        $document = (new CatalogDocumentBuilder())->build($product);

        $this->assertSame([
            ['id' => 1, 'name' => 'Root'],
            ['id' => 2, 'name' => 'Child'],
        ], $document['categories']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $relations
     */
    private function makeProduct(array $attributes, array $relations = []): Product
    {
        $defaults = [
            'brand_id' => 0,
            'season_id' => 0,
            'collection_id' => 0,
            'color_txt' => null,
            'rating' => 0,
            'newness_rating' => 0,
            'season_rating' => 0,
            'sale_rating' => 0,
            'created_at' => now(),
        ];

        $product = new Product(array_merge($defaults, $attributes));
        $product->id = (int)$attributes['id'];

        $product->setRelation('category', $relations['category'] ?? null);
        $product->setRelation('brand', $relations['brand'] ?? null);
        $product->setRelation('sizes', $relations['sizes'] ?? new Collection());
        $product->setRelation('colors', $relations['colors'] ?? new Collection());
        $product->setRelation('fabrics', $relations['fabrics'] ?? new Collection());
        $product->setRelation('heels', $relations['heels'] ?? new Collection());
        $product->setRelation('styles', $relations['styles'] ?? new Collection());
        $product->setRelation('tags', $relations['tags'] ?? new Collection());

        return $product;
    }

    private function makeCategory(int $id, string $title, ?Category $parent = null): Category
    {
        $category = new Category(['title' => $title]);
        $category->id = $id;
        $category->setRelation('parentCategory', $parent);

        return $category;
    }

    private function makeBrand(int $id, string $name): Brand
    {
        $brand = new Brand(['name' => $name]);
        $brand->id = $id;

        return $brand;
    }

    /**
     * @template T of Size|Color
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function makeSizedModel(string $class, int $id, ?string $name = null): Size|Color
    {
        $model = new $class;
        $model->id = $id;
        if ($name !== null) {
            $model->name = $name;
        }

        return $model;
    }

    private function makeTag(int $id, string $name): Tag
    {
        $tag = new Tag(['name' => $name]);
        $tag->id = $id;

        return $tag;
    }
}
