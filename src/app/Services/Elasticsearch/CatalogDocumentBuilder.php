<?php

namespace App\Services\Elasticsearch;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

        $categoryChain = $this->categoryChain($product->category);

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,

            'brand' => [
                'id' => (int)($product->brand->id ?? $product->brand_id),
                'name' => $product->brand->name ?? '',
            ],
            'categories' => array_map(
                static fn (array $item): array => [
                    'id' => $item['id'],
                    'name' => $item['title'],
                ],
                $categoryChain,
            ),
            'short_name' => $product->category !== null
                ? $product->shortName()
                : (string)$product->id,
            'color_txt' => $product->color_txt,
            'sizes' => $this->namedObjects($product->sizes),
            'colors' => $this->namedObjects($product->colors),
            'tags' => $this->namedObjects($product->tags),

            'price' => (float)$product->price,
            'old_price' => (float)$product->old_price,
            'has_discount' => $hasDiscount,
            'is_new' => $isNew,
            'status_slugs' => $statusSlugs,

            'season_id' => $product->season_id,
            'collection_id' => $product->collection_id,

            // Filter-only attributes (no text search yet).
            'fabric_ids' => $product->fabrics->pluck('id')->map(intval(...))->values()->all(),
            'heel_ids' => $product->heels->pluck('id')->map(intval(...))->values()->all(),
            'style_ids' => $product->styles->pluck('id')->map(intval(...))->values()->all(),

            'rating' => (int)$product->rating,
            'newness_rating' => (int)$product->newness_rating,
            'season_rating' => (int)$product->season_rating,
            'sale_rating' => (int)$product->sale_rating,

            'created_at' => $this->formatDate($product->created_at),
        ];
    }

    /**
     * @template T of object{id: int|string, name?: string|null}
     *
     * @param  Collection<int, T>  $items
     * @return list<array{id: int, name: string}>
     */
    protected function namedObjects(Collection $items): array
    {
        return $items
            ->map(static fn (object $item): array => [
                'id' => (int)$item->id,
                'name' => (string)($item->name ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, title: string}>
     */
    protected function categoryChain(?Category $category): array
    {
        if ($category === null) {
            return [];
        }

        $chain = [];
        $current = $category;
        while ($current !== null) {
            array_unshift($chain, [
                'id' => $current->id,
                'title' => (string)($current->title ?? ''),
            ]);
            $current = $current->parentCategory;
        }

        return $chain;
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
