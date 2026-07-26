<?php

namespace App\Listeners\Elasticsearch;

use App\Events\Products\ProductCreated;
use App\Events\Products\ProductUpdated;
use App\Jobs\Elasticsearch\UpsertCatalogProductJob;

class SyncCatalogProduct
{
    public function handle(ProductCreated|ProductUpdated $event): void
    {
        UpsertCatalogProductJob::dispatch($event->product->id);
    }
}
