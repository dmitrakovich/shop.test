<?php

namespace App\Jobs\Search;

use App\ElasticSearch\Indices\Product\ProductDocument;
use App\ElasticSearch\Indices\Product\ProductIndexWriter;
use App\Jobs\AbstractJob;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Sync a single product document to Elasticsearch (index or delete).
 */
class SyncProductToElasticsearchJob extends AbstractJob implements ShouldBeUnique
{
    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Backoff seconds between retries.
     *
     * @var list<int>
     */
    public array $backoff = [5, 30, 60];

    /**
     * Seconds the unique lock should be maintained.
     */
    public int $uniqueFor = 60;

    /**
     * @param  int  $productId  Product primary key
     */
    public function __construct(
        public readonly int $productId,
    ) {
        $this->jobName = 'SyncProductToElasticsearch';
        $this->contextVars = ['productId'];
    }

    /**
     * Unique job id per product to coalesce rapid updates.
     */
    public function uniqueId(): string
    {
        return (string)$this->productId;
    }

    /**
     * Index the product when eligible, otherwise remove it from the index.
     */
    public function handle(ProductIndexWriter $writer, ProductDocument $document): void
    {
        if (!config('services.search.enabled')) {
            return;
        }

        $product = Product::query()
            ->withTrashed()
            ->whereKey($this->productId)
            ->first();

        if ($product === null || $product->trashed()) {
            $writer->bulkDelete([$this->productId]);
            $this->log("Deleted product {$this->productId} from Elasticsearch");

            return;
        }

        $indexable = Product::query()
            ->forElasticsearchDocument()
            ->whereKey($this->productId)
            ->first();

        if ($indexable === null) {
            $writer->bulkDelete([$this->productId]);
            $this->log("Removed ineligible product {$this->productId} from Elasticsearch");

            return;
        }

        $payload = $document->from($indexable);
        if ($payload === []) {
            $writer->bulkDelete([$this->productId]);
            $this->log("Removed empty-document product {$this->productId} from Elasticsearch");

            return;
        }

        $response = $writer->bulkIndex([$payload]);
        if (!empty($response['errors'])) {
            throw new \RuntimeException(
                'Elasticsearch bulk index failed for product '.$this->productId
            );
        }

        $this->log("Indexed product {$this->productId}");
    }
}
