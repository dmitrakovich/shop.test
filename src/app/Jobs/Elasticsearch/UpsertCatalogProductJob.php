<?php

namespace App\Jobs\Elasticsearch;

use App\Enums\Queue;
use App\Jobs\AbstractJob;
use App\Models\Product;
use App\Services\Elasticsearch\CatalogDocumentBuilder;
use App\Services\Elasticsearch\CatalogIndexer;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpsertCatalogProductJob extends AbstractJob implements ShouldBeUnique
{
    /**
     * Seconds the unique lock should be maintained.
     */
    public int $uniqueFor = 60;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Backoff seconds between retries (short ES blips).
     *
     * @var list<int>
     */
    public array $backoff = [10, 30];

    /**
     * @var list<string>
     */
    protected $contextVars = ['productId'];

    public function __construct(public readonly int $productId)
    {
        $this->onQueue(Queue::Elasticsearch);
        $this->jobName = 'UpsertCatalogProduct';
    }

    public function uniqueId(): string
    {
        return (string)$this->productId;
    }

    public function handle(CatalogIndexer $indexer, CatalogDocumentBuilder $builder): void
    {
        $product = Product::withTrashed()
            ->with($builder->relations())
            ->find($this->productId);

        if ($product === null || $product->trashed()) {
            $indexer->delete([$this->productId]);

            return;
        }

        $indexer->upsert([$product]);
    }
}
