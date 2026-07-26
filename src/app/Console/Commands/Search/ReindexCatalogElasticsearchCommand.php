<?php

namespace App\Console\Commands\Search;

use App\ElasticSearch\Indices\Product\ProductDocument;
use App\ElasticSearch\Indices\Product\ProductIndex;
use App\ElasticSearch\Indices\Product\ProductIndexWriter;
use App\Models\Product;
use Illuminate\Console\Command;

class ReindexCatalogElasticsearchCommand extends Command
{
    protected $signature = 'search:reindex-catalog
                            {--chunk=250 : Chunk size for bulk indexing}';

    protected $description = 'Delete product index in Elasticsearch, recreate it, and index eligible products';

    /**
     * Fully rebuild the catalog index.
     */
    public function handle(
        ProductIndex $index,
        ProductIndexWriter $writer,
        ProductDocument $document,
    ): int {
        if (!config('services.search.enabled')) {
            $this->error('Elasticsearch is disabled (services.search.enabled / ELASTICSEARCH_ENABLED).');

            return Command::FAILURE;
        }

        $chunk = max(1, min(1000, (int)$this->option('chunk')));

        $this->info('Index: '.$index->name());

        $this->warn('Deleting index (if exists)...');
        $index->deleteIfExists();

        $this->info('Creating index and mapping...');
        $index->create();

        $total = Product::query()->forElasticsearchDocument()->count();
        $this->info("To index: {$total} products (chunk {$chunk})");

        if ($total === 0) {
            $index->refresh();
            Product::query()->update(['pending_es_sync' => false]);
            $this->warn('No products match indexing conditions (brand, category, media, price > 0).');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $bulkErrors = 0;
        $skipped = 0;
        $firstErrors = [];

        Product::query()
            ->forElasticsearchDocument()
            ->orderBy('id')
            ->chunkById($chunk, function ($products) use ($writer, $document, $bar, &$bulkErrors, &$skipped, &$firstErrors): void {
                $documents = [];
                foreach ($products as $product) {
                    $doc = $document->from($product);
                    if ($doc === []) {
                        $skipped++;

                        continue;
                    }
                    $documents[] = $doc;
                }

                if ($documents !== []) {
                    $response = $writer->bulkIndex($documents);
                    if (!empty($response['errors'])) {
                        foreach ($response['items'] ?? [] as $item) {
                            if (!empty($item['index']['error'])) {
                                $bulkErrors++;
                                if (count($firstErrors) < 5) {
                                    $firstErrors[] = $item['index']['error'];
                                }
                            }
                        }
                    }
                }

                $bar->advance($products->count());
            });

        $bar->finish();
        $this->newLine(2);

        $index->refresh();

        if ($bulkErrors === 0) {
            Product::query()->update(['pending_es_sync' => false]);
        }

        $this->info('Done.');
        if ($skipped > 0) {
            $this->warn("Skipped documents (empty ProductDocument): {$skipped}");
        }
        if ($bulkErrors > 0) {
            $this->error("Bulk errors: {$bulkErrors}");
            foreach ($firstErrors as $err) {
                $this->line(json_encode($err, JSON_UNESCAPED_UNICODE));
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
