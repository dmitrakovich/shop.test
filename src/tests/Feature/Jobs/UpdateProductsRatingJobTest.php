<?php

namespace Tests\Feature\Jobs;

use App\Enums\Config\ConfigKey;
use App\Jobs\UpdateProductsRatingJob;
use App\Models\Config;
use App\Models\Product;
use App\Models\RatingAlgorithm;
use App\Services\Api\Yandex\MetrikaService;
use App\Services\Elasticsearch\CatalogIndexer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateProductsRatingJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_updates_ratings_and_reindexes_catalog_products(): void
    {
        $this->travelTo(now()->midDay());

        $algorithm = $this->createAlgorithm(createdAtCoefficient: 2);
        $this->saveRatingConfig($algorithm);
        $product = Product::factory()->create([
            'price' => 1000,
            'old_price' => 0,
            'rating' => 1,
            'newness_rating' => 1,
            'season_rating' => 1,
            'sale_rating' => 1,
            'created_at' => now(),
        ]);

        $this->mock(MetrikaService::class, function (MockInterface $mock) use ($product): void {
            $mock->shouldReceive('fetchProductMetrics')
                ->once()
                ->with([$product->id])
                ->andReturn([
                    'views' => [$product->id => 0.0],
                    'purchases' => [$product->id => 0.0],
                    'carts' => [$product->id => 0.0],
                ]);
        });
        $this->mock(CatalogIndexer::class, function (MockInterface $mock) use ($product): void {
            $mock->shouldReceive('syncProductIds')
                ->once()
                ->with([$product->id]);
        });

        UpdateProductsRatingJob::dispatchSync();

        $product->refresh();

        $this->assertSame(200, $product->rating);
        $this->assertSame(200, $product->newness_rating);
        $this->assertSame(200, $product->season_rating);
        $this->assertSame(200, $product->sale_rating);
        $this->assertNotNull(Config::findByKeyOrFail(ConfigKey::Rating)->config['last_update']);
    }

    public function test_job_skips_catalog_reindex_when_there_are_no_products(): void
    {
        $this->saveRatingConfig($this->createAlgorithm());

        $this->mock(MetrikaService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('fetchProductMetrics');
        });
        $this->mock(CatalogIndexer::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('syncProductIds');
        });

        UpdateProductsRatingJob::dispatchSync();

        $this->assertNotNull(Config::findByKeyOrFail(ConfigKey::Rating)->config['last_update']);
    }

    private function createAlgorithm(int $createdAtCoefficient = 0): RatingAlgorithm
    {
        return RatingAlgorithm::query()->create([
            'name' => 'Test algorithm',
            'created_at_coefficient' => $createdAtCoefficient,
        ]);
    }

    private function saveRatingConfig(RatingAlgorithm $algorithm): void
    {
        Config::query()->updateOrCreate(
            ['key' => ConfigKey::Rating],
            [
                'config' => [
                    'popularity_algorithm_id' => $algorithm->id,
                    'newness_algorithm_id' => $algorithm->id,
                    'season_algorithm_id' => $algorithm->id,
                    'sale_algorithm_id' => $algorithm->id,
                ],
            ],
        );
    }
}
