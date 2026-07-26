<?php

namespace App\ElasticSearch\Indices\Product\Transformer;

use stdClass;

class DefaultProductTransformer
{
    /**
     * Transform an Elasticsearch hit into a catalog document object.
     *
     * @param  array<string, mixed>  $product  ES hit
     * @return stdClass Catalog document
     */
    public function transform(array $product): stdClass
    {
        $source = $product['_source'] ?? [];

        $item = new stdClass;
        $item->id = (int)($source['id'] ?? 0);
        $item->slug = (string)($source['slug'] ?? '');
        $item->sku = isset($source['sku']) ? (string)$source['sku'] : null;
        $item->price = isset($source['price']) ? (float)$source['price'] : null;
        $item->old_price = isset($source['old_price']) ? (float)$source['old_price'] : null;
        $item->short_name = (string)($source['short_name'] ?? '');
        $item->rating = (int)($source['rating'] ?? 0);
        $item->newness_rating = (int)($source['newness_rating'] ?? 0);
        $item->season_rating = (int)($source['season_rating'] ?? 0);
        $item->sale_rating = (int)($source['sale_rating'] ?? 0);
        $item->created_at = (string)($source['created_at'] ?? '');
        $item->brand = $this->mapNamedEntity($source['brand'] ?? []);
        $item->collection = $this->mapNamedEntity($source['collection'] ?? []);
        $item->categories = $this->mapNamedEntities($source['categories'] ?? []);
        $item->sizes = $this->mapNamedEntities($source['sizes'] ?? []);
        $item->colors = $this->mapNamedEntities($source['colors'] ?? []);
        $item->fabrics = $this->mapNamedEntities($source['fabrics'] ?? []);
        $item->heels = $this->mapNamedEntities($source['heels'] ?? []);
        $item->styles = $this->mapNamedEntities($source['styles'] ?? []);
        $item->tags = $this->mapNamedEntities($source['tags'] ?? []);
        $item->seasons = $this->mapNamedEntities($source['seasons'] ?? []);
        $item->statuses = $source['statuses'] ?? [];
        $item->images = json_decode(json_encode($source['images'] ?? []), false);

        return $item;
    }

    /**
     * Map a single named entity from _source.
     *
     * @param  array<string, mixed>  $entity  Entity data
     * @return object{id: int, name: string, slug: string} Entity object
     */
    private function mapNamedEntity(array $entity): object
    {
        return (object)[
            'id' => (int)($entity['id'] ?? 0),
            'name' => (string)($entity['name'] ?? ''),
            'slug' => (string)($entity['slug'] ?? ''),
        ];
    }

    /**
     * Map a list of named entities from _source.
     *
     * @param  list<array<string, mixed>>  $entities  Entities
     * @return list<object{id: int, name: string, slug: string}> Mapped entities
     */
    private function mapNamedEntities(array $entities): array
    {
        return array_values(array_map(function ($entity) {
            $entity = (array)$entity;

            return $this->mapNamedEntity($entity);
        }, $entities));
    }
}
