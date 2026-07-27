<?php

namespace App\Services\Elasticsearch;

use App\Models\Product;
use Elastic\Adapter\Documents\Document;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Client\ClientBuilderInterface;
use Illuminate\Support\Collection;

class CatalogIndexer
{
    private const int SYNC_CHUNK_SIZE = 200;

    public function __construct(
        private readonly DocumentManager $documents,
        private readonly CatalogDocumentBuilder $builder,
        private readonly ClientBuilderInterface $clientBuilder,
    ) {}

    public function alias(): string
    {
        return (string)config('catalog.elasticsearch.alias');
    }

    /**
     * Bulk upsert / delete by product id (missing or soft-deleted → delete).
     *
     * @param  list<int|string>  $productIds
     */
    public function syncProductIds(array $productIds): void
    {
        foreach (array_chunk($productIds, self::SYNC_CHUNK_SIZE) as $chunk) {
            $products = Product::withTrashed()
                ->with($this->builder->relations())
                ->whereIn('id', $chunk)
                ->get();

            $upsert = [];
            $deleteIds = array_values(array_diff($chunk, $products->modelKeys()));

            $products->each(function (Product $product) use (&$upsert, &$deleteIds): void {
                if ($product->trashed()) {
                    $deleteIds[] = $product->id;
                } else {
                    $upsert[] = $product;
                }
            });

            $this->delete($deleteIds);
            $this->upsert($upsert);
        }
    }

    /**
     * @param  iterable<\App\Models\Product>  $products
     */
    public function upsert(iterable $products, bool $refresh = false): void
    {
        $documents = [];

        foreach ($products as $product) {
            $documents[] = new Document(
                (string)$product->id,
                $this->builder->build($product),
            );
        }

        if ($documents === []) {
            return;
        }

        $this->documents->index($this->alias(), Collection::make($documents), $refresh);
    }

    /**
     * @param  list<int|string>  $productIds
     */
    public function delete(array $productIds, bool $refresh = false): void
    {
        $ids = array_values(array_unique(array_map(
            static fn (int|string $id): string => (string)$id,
            $productIds,
        )));

        if ($ids === []) {
            return;
        }

        $this->documents->delete($this->alias(), $ids, $refresh);
    }

    public function deleteAll(bool $refresh = false): void
    {
        $this->documents->deleteByQuery(
            $this->alias(),
            ['match_all' => (object)[]],
            $refresh,
        );
    }

    public function refresh(): void
    {
        $this->clientBuilder->default()->indices()->refresh([
            'index' => $this->alias(),
        ]);
    }
}
