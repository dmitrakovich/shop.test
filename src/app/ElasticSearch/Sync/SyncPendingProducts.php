<?php

namespace App\ElasticSearch\Sync;

use App\ElasticSearch\Indices\Product\ProductDocument;
use App\ElasticSearch\Indices\Product\ProductIndex;
use App\ElasticSearch\Indices\Product\ProductIndexWriter;
use App\Models\Product;

/**
 * Bulk-sync products flagged pending_es_sync (mass upserts without Eloquent events).
 */
class SyncPendingProducts
{
    /**
     * @param  ProductIndex  $index  Product index
     * @param  ProductIndexWriter  $writer  Bulk writer
     * @param  ProductDocument  $document  Document builder
     */
    public function __construct(
        private readonly ProductIndex $index,
        private readonly ProductIndexWriter $writer,
        private readonly ProductDocument $document,
    ) {}

    /**
     * Sync only products with pending_es_sync into Elasticsearch.
     *
     * @param  int  $chunk  Chunk size
     * @param  (callable(int): void)|null  $onChunk  Progress callback
     * @return SyncPendingResult Sync result
     */
    public function execute(int $chunk = 250, ?callable $onChunk = null): SyncPendingResult
    {
        if (!config('services.search.enabled')) {
            return new SyncPendingResult;
        }

        $chunk = max(1, min(1000, $chunk));

        $pendingQuery = Product::query()
            ->withTrashed()
            ->pendingEsSync()
            ->orderBy('products.id');

        $pendingTotal = (clone $pendingQuery)->count();
        if ($pendingTotal === 0) {
            return new SyncPendingResult;
        }

        $indexed = 0;
        $deleted = 0;
        $skipped = 0;
        $bulkErrors = 0;
        $firstErrors = [];

        $pendingQuery->select('products.id')->chunkById($chunk, function ($products) use (
            &$indexed,
            &$deleted,
            &$skipped,
            &$bulkErrors,
            &$firstErrors,
            $onChunk,
        ): void {
            $ids = $products->pluck('id')->all();

            $indexable = Product::query()
                ->whereIn('products.id', $ids)
                ->forElasticsearchDocument()
                ->get();

            $indexableIds = $indexable->pluck('id')->all();
            $deleteIds = array_values(array_diff($ids, $indexableIds));

            $documents = [];
            foreach ($indexable as $product) {
                $doc = $this->document->from($product);
                if ($doc === []) {
                    $skipped++;

                    continue;
                }
                $documents[] = $doc;
            }

            if ($documents !== []) {
                $response = $this->writer->bulkIndex($documents);
                $successfulIds = $this->successfulBulkIndexedProductIds($documents, $response);
                if ($successfulIds !== []) {
                    Product::query()->whereIn('id', $successfulIds)->update(['pending_es_sync' => false]);
                    $indexed += count($successfulIds);
                }
                $bulkErrors += $this->collectBulkIndexErrors($response, $firstErrors);
            }

            if ($deleteIds !== []) {
                $response = $this->writer->bulkDelete($deleteIds);
                $successfulIds = $this->successfulBulkDeletedProductIds($deleteIds, $response);
                if ($successfulIds !== []) {
                    Product::query()->whereIn('id', $successfulIds)->update(['pending_es_sync' => false]);
                    $deleted += count($successfulIds);
                }
                $bulkErrors += $this->collectBulkDeleteErrors($response, $firstErrors);
            }

            if ($onChunk !== null) {
                $onChunk($products->count());
            }
        });

        $this->index->refresh();

        return new SyncPendingResult(
            pendingTotal: $pendingTotal,
            indexed: $indexed,
            deleted: $deleted,
            skipped: $skipped,
            bulkErrors: $bulkErrors,
            firstErrors: $firstErrors,
        );
    }

    /**
     * Count products with pending_es_sync.
     */
    public function pendingCount(): int
    {
        return Product::query()->withTrashed()->pendingEsSync()->count();
    }

    /**
     * @param  list<array<string, mixed>>  $documents
     * @param  array<string, mixed>  $response
     * @return list<int>
     */
    private function successfulBulkIndexedProductIds(array $documents, array $response): array
    {
        if ($documents === []) {
            return [];
        }

        if (empty($response['errors'])) {
            return array_values(array_map(static fn (array $d): int => (int)$d['id'], $documents));
        }

        $ids = [];
        $items = $response['items'] ?? [];
        foreach ($documents as $i => $doc) {
            $item = $items[$i] ?? [];
            if (empty($item['index']['error'])) {
                $ids[] = (int)$doc['id'];
            }
        }

        return $ids;
    }

    /**
     * @param  list<int>  $ids
     * @param  array<string, mixed>  $response
     * @return list<int>
     */
    private function successfulBulkDeletedProductIds(array $ids, array $response): array
    {
        if ($ids === []) {
            return [];
        }

        if (empty($response['errors'])) {
            return $ids;
        }

        $successful = [];
        $items = $response['items'] ?? [];
        foreach ($ids as $i => $id) {
            $item = $items[$i] ?? [];
            $error = $item['delete']['error'] ?? null;
            if ($error === null || $this->isElasticsearchNotFoundError($error)) {
                $successful[] = $id;
            }
        }

        return $successful;
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  list<array<string, mixed>>  $firstErrors
     */
    private function collectBulkIndexErrors(array $response, array &$firstErrors): int
    {
        if (empty($response['errors'])) {
            return 0;
        }

        $count = 0;
        foreach ($response['items'] ?? [] as $item) {
            if (empty($item['index']['error'])) {
                continue;
            }
            $count++;
            if (count($firstErrors) < 5) {
                $firstErrors[] = $item['index']['error'];
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  list<array<string, mixed>>  $firstErrors
     */
    private function collectBulkDeleteErrors(array $response, array &$firstErrors): int
    {
        if (empty($response['errors'])) {
            return 0;
        }

        $count = 0;
        foreach ($response['items'] ?? [] as $item) {
            $error = $item['delete']['error'] ?? null;
            if ($error === null || $this->isElasticsearchNotFoundError($error)) {
                continue;
            }
            $count++;
            if (count($firstErrors) < 5) {
                $firstErrors[] = $error;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $error
     */
    private function isElasticsearchNotFoundError(array $error): bool
    {
        $type = $error['type'] ?? '';

        return $type === 'index_not_found_exception'
            || $type === 'document_missing_exception'
            || ($error['result'] ?? '') === 'not_found';
    }
}
