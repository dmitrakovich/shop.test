<?php

namespace Tests\Unit\Elastic;

use App\Models\Product;
use App\Services\Elasticsearch\CatalogDocumentBuilder;
use App\Services\Elasticsearch\CatalogIndexer;
use Elastic\Adapter\Documents\Document;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Client\ClientBuilderInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CatalogIndexerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_upsert_indexes_documents_via_alias(): void
    {
        config(['catalog.elasticsearch.alias' => 'catalog']);

        $product = new Product([
            'sku' => 'SKU-1',
            'slug' => 'sku-1',
            'price' => 10,
            'old_price' => 0,
            'brand_id' => 0,
            'season_id' => 0,
            'collection_id' => 0,
            'rating' => 0,
            'newness_rating' => 0,
            'season_rating' => 0,
            'sale_rating' => 0,
            'created_at' => now(),
        ]);
        $product->id = 42;
        $product->setRelation('category', null);
        $product->setRelation('brand', null);
        $product->setRelation('sizes', new Collection());
        $product->setRelation('colors', new Collection());
        $product->setRelation('fabrics', new Collection());
        $product->setRelation('heels', new Collection());
        $product->setRelation('styles', new Collection());
        $product->setRelation('tags', new Collection());

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('index')
            ->once()
            ->withArgs(function (string $alias, Collection $docs, bool $refresh): bool {
                $this->assertSame('catalog', $alias);
                $this->assertFalse($refresh);
                $this->assertCount(1, $docs);
                /** @var Document $document */
                $document = $docs->first();
                $this->assertSame('42', $document->id());
                $this->assertSame(42, $document->content('id'));

                return true;
            })
            ->andReturnSelf();

        $indexer = new CatalogIndexer(
            $documents,
            new CatalogDocumentBuilder(),
            Mockery::mock(ClientBuilderInterface::class),
        );

        $indexer->upsert([$product]);
    }

    public function test_upsert_skips_empty_collection(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldNotReceive('index');

        $indexer = new CatalogIndexer(
            $documents,
            new CatalogDocumentBuilder(),
            Mockery::mock(ClientBuilderInterface::class),
        );

        $indexer->upsert([]);
    }

    public function test_delete_casts_ids_to_strings(): void
    {
        config(['catalog.elasticsearch.alias' => 'catalog']);

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('delete')
            ->once()
            ->with('catalog', ['1', '2'], false)
            ->andReturnSelf();

        $indexer = new CatalogIndexer(
            $documents,
            new CatalogDocumentBuilder(),
            Mockery::mock(ClientBuilderInterface::class),
        );

        $indexer->delete([1, 2, 1]);
    }

    public function test_delete_all_uses_match_all_query(): void
    {
        config(['catalog.elasticsearch.alias' => 'catalog']);

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('deleteByQuery')
            ->once()
            ->withArgs(function (string $alias, array $query, bool $refresh): bool {
                $this->assertSame('catalog', $alias);
                $this->assertFalse($refresh);
                $this->assertInstanceOf(\stdClass::class, $query['match_all']);

                return true;
            })
            ->andReturnSelf();

        $indexer = new CatalogIndexer(
            $documents,
            new CatalogDocumentBuilder(),
            Mockery::mock(ClientBuilderInterface::class),
        );

        $indexer->deleteAll();
    }
}
