<?php

namespace Tests\Unit\Elastic;

use App\Events\Products\ProductCreated;
use App\Events\Products\ProductUpdated;
use App\Jobs\Elasticsearch\UpsertCatalogProductJob;
use App\Listeners\Elasticsearch\SyncCatalogProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCatalogProductListenerTest extends TestCase
{
    public function test_it_dispatches_upsert_job_on_product_created(): void
    {
        Bus::fake([UpsertCatalogProductJob::class]);

        $product = new Product();
        $product->id = 15;

        (new SyncCatalogProduct())->handle(new ProductCreated($product));

        Bus::assertDispatched(
            UpsertCatalogProductJob::class,
            fn (UpsertCatalogProductJob $job): bool => $job->productId === 15
                && $job->afterCommit === true,
        );
    }

    public function test_it_dispatches_upsert_job_on_product_updated(): void
    {
        Bus::fake([UpsertCatalogProductJob::class]);

        $product = new Product();
        $product->id = 27;

        (new SyncCatalogProduct())->handle(new ProductUpdated($product));

        Bus::assertDispatched(
            UpsertCatalogProductJob::class,
            fn (UpsertCatalogProductJob $job): bool => $job->productId === 27
                && $job->afterCommit === true,
        );
    }
}
