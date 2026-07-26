<?php

namespace App\ElasticSearch\Indices\Product;

use App\Models\Product;

/**
 * Builds Elasticsearch documents from Product models.
 */
class ProductDocument
{
    /**
     * Build a document from a product model or load by id.
     *
     * @param  Product|null  $product  Eager-loaded product
     * @param  int|null  $id  Id to load when model is missing
     * @return array<string, mixed> Document or empty array
     */
    public function from(?Product $product = null, ?int $id = null): array
    {
        if (($product === null || !isset($product->id)) && $id) {
            $product = Product::query()->forElasticsearchDocument()->whereKey($id)->first();
        }

        if ($product === null || !isset($product->id)) {
            return [];
        }

        $brand = $product->brand;
        if ($brand === null) {
            return [];
        }

        $categories = [];
        if ($product->category !== null) {
            $categoryChain = [];
            if (method_exists($product->category, 'ancestors')) {
                foreach ($product->category->ancestors as $ancestor) {
                    $categoryChain[] = $ancestor;
                }
            }
            $categoryChain[] = $product->category;

            foreach ($categoryChain as $category) {
                $categories[] = [
                    'id' => $category->id,
                    'name' => $category->title,
                    'slug' => $category->slug,
                ];
            }
        }

        $collection = null;
        if ($product->collection !== null) {
            $collection = [
                'id' => $product->collection->id,
                'name' => $product->collection->name,
                'slug' => $product->collection->slug,
            ];
        }

        $seasons = [];
        if ($product->season !== null) {
            $seasons[] = [
                'id' => $product->season->id,
                'name' => $product->season->name,
                'slug' => $product->season->slug,
            ];
        }

        $mapAttributes = static function ($items): array {
            $result = [];
            foreach ($items ?? [] as $item) {
                $result[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                ];
            }

            return $result;
        };

        $statuses = [];
        if ($product->old_price == 0 || $product->old_price === null) {
            $statuses[] = 'st-new';
        }
        if ($product->old_price && $product->price < $product->old_price) {
            $statuses[] = 'st-sale';
        }

        $images = [];
        foreach ($product->getMedia('default') as $media) {
            $path = method_exists($media, 'getPathRelativeToRoot')
                ? $media->getPathRelativeToRoot()
                : $media->file_name;

            $images[] = [
                'id' => $media->id,
                'path' => $path,
                'url' => $media->getUrl('small'),
            ];
        }

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_name' => $product->shortName(),
            'description' => $product->description,
            'price' => (float)$product->price,
            'old_price' => (float)$product->old_price,
            'rating' => (int)$product->rating,
            'newness_rating' => (int)$product->newness_rating,
            'season_rating' => (int)$product->season_rating,
            'sale_rating' => (int)$product->sale_rating,
            'created_at' => $product->created_at?->toIso8601String(),
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ],
            'categories' => $categories,
            'collection' => $collection,
            'seasons' => $seasons,
            'sizes' => $mapAttributes($product->sizes),
            'colors' => $mapAttributes($product->colors),
            'fabrics' => $mapAttributes($product->fabrics),
            'heels' => $mapAttributes($product->heels),
            'styles' => $mapAttributes($product->styles),
            'tags' => $mapAttributes($product->tags),
            'statuses' => $statuses,
            'images' => $images,
        ];
    }
}
