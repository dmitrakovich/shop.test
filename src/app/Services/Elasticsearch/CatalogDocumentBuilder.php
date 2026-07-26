<?php

namespace App\Services\Elasticsearch;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;

class CatalogDocumentBuilder
{
    /**
     * Relations required to build a complete catalog document.
     *
     * @return list<string>
     */
    public function relations(): array
    {
        return [
            'category.parentCategory',
            'brand:id,name',
            'sizes:id,name',
            'colors:id,name',
            'fabrics:id',
            'heels:id',
            'styles:id',
            'tags:id,name',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Product $product): array
    {
        $hasDiscount = $product->price < $product->old_price;
        $isNew = (float)$product->old_price === 0.0;

        $statusSlugs = [];
        if ($isNew) {
            $statusSlugs[] = 'st-new';
        }
        if ($hasDiscount) {
            $statusSlugs[] = 'st-sale';
        }

        $brandName = $product->brand->name ?? '';
        $categoryTitle = $product->category->title ?? '';
        $tagNames = $product->tags->pluck('name')->filter()->implode(' ');
        $sizeNames = $product->sizes->pluck('name')->filter()->implode(' ');
        $colorNames = $product->colors->pluck('name')->filter()->implode(' ');

        $searchParts = array_filter([
            $product->sku,
            (string)$product->id,
            $brandName,
            $categoryTitle,
            $product->color_txt,
            $colorNames,
            $tagNames,
            $sizeNames,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'color_txt' => $product->color_txt,
            'search_text' => implode(' ', $searchParts),

            'price' => (float)$product->price,
            'old_price' => (float)$product->old_price,
            'has_discount' => $hasDiscount,
            'is_new' => $isNew,
            'status_slugs' => $statusSlugs,

            'category_id' => $product->category_id,
            'category_ids' => $this->categoryIdsWithAncestors($product->category),
            'brand_id' => $product->brand_id,
            'season_id' => $product->season_id,
            'collection_id' => $product->collection_id,

            'size_ids' => $product->sizes->pluck('id')->map(intval(...))->values()->all(),
            'color_ids' => $product->colors->pluck('id')->map(intval(...))->values()->all(),
            'fabric_ids' => $product->fabrics->pluck('id')->map(intval(...))->values()->all(),
            'heel_ids' => $product->heels->pluck('id')->map(intval(...))->values()->all(),
            'style_ids' => $product->styles->pluck('id')->map(intval(...))->values()->all(),
            'tag_ids' => $product->tags->pluck('id')->map(intval(...))->values()->all(),

            'rating' => (int)$product->rating,
            'newness_rating' => (int)$product->newness_rating,
            'season_rating' => (int)$product->season_rating,
            'sale_rating' => (int)$product->sale_rating,

            'created_at' => $this->formatDate($product->created_at),
        ];
    }

    /**
     * Leaf category id plus ancestors (root → … → leaf), for parent-category filters.
     *
     * @return list<int>
     */
    protected function categoryIdsWithAncestors(?Category $category): array
    {
        if ($category === null) {
            return [];
        }

        $ids = [];
        $current = $category;
        while ($current !== null) {
            array_unshift($ids, $current->id);
            $current = $current->parentCategory;
        }

        return array_values(array_unique($ids));
    }

    protected function formatDate(mixed $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value)->toIso8601String();
        }

        return null;
    }
}
