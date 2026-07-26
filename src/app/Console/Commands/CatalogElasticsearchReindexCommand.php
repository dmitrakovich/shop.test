<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Elasticsearch\CatalogDocumentBuilder;
use App\Services\Elasticsearch\CatalogIndexer;
use Illuminate\Console\Command;

class CatalogElasticsearchReindexCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'catalog:elasticsearch-reindex
                            {--chunk=200 : Number of products per bulk request}
                            {--fresh : Delete all documents before indexing}';

    /**
     * @var string
     */
    protected $description = 'Reindex listable products into the catalog Elasticsearch alias';

    public function handle(CatalogIndexer $indexer, CatalogDocumentBuilder $builder): int
    {
        $chunkSize = max(1, (int)$this->option('chunk'));

        if ($this->option('fresh')) {
            $this->info('Clearing documents from alias [' . $indexer->alias() . ']…');
            $indexer->deleteAll();
        }

        $query = Product::query()->with($builder->relations());
        $total = (clone $query)->count();

        $this->info("Indexing {$total} products into [{$indexer->alias()}]…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($chunkSize, function ($products) use ($indexer, $bar): void {
            $indexer->upsert($products);
            $bar->advance($products->count());
        });

        $bar->finish();
        $this->newLine();

        $indexer->refresh();
        $this->info('Catalog reindex complete.');

        return self::SUCCESS;
    }
}
