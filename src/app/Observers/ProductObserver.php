<?php

namespace App\Observers;

use App\Jobs\Search\SyncProductToElasticsearchJob;
use App\Models\Product;

class ProductObserver
{
    /**
     * After save, sync the product to Elasticsearch via queue.
     */
    public function saved(Product $product): void
    {
        $this->dispatchSync((int)$product->id);
    }

    /**
     * After soft delete, remove the product from Elasticsearch via queue.
     */
    public function deleted(Product $product): void
    {
        $this->dispatchSync((int)$product->id);
    }

    /**
     * After restore, re-index the product via queue.
     */
    public function restored(Product $product): void
    {
        $this->dispatchSync((int)$product->id);
    }

    /**
     * After force delete, remove the document from Elasticsearch via queue.
     */
    public function forceDeleted(Product $product): void
    {
        $this->dispatchSync((int)$product->id);
    }

    /**
     * Dispatch a unique sync job for the product.
     */
    private function dispatchSync(int $productId): void
    {
        if (!config('services.search.enabled') || $productId <= 0) {
            return;
        }

        SyncProductToElasticsearchJob::dispatch($productId);
    }
}
