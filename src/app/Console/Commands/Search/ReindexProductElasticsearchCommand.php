<?php

namespace App\Console\Commands\Search;

use App\ElasticSearch\Sync\SyncPendingProducts;
use Illuminate\Console\Command;

class ReindexProductElasticsearchCommand extends Command
{
    protected $signature = 'search:reindex-product
                            {--chunk=250 : Chunk size for Elasticsearch bulk writes}';

    protected $description = 'Bulk-sync products flagged pending_es_sync (mass upserts) to Elasticsearch';

    /**
     * Run pending product synchronization.
     */
    public function handle(SyncPendingProducts $sync): int
    {
        if (!config('services.search.enabled')) {
            $this->error('Elasticsearch is disabled (services.search.enabled / ELASTICSEARCH_ENABLED).');

            return Command::FAILURE;
        }

        $chunk = max(1, min(1000, (int)$this->option('chunk')));
        $pendingBefore = $sync->pendingCount();

        if ($pendingBefore === 0) {
            $this->info('No products with pending_es_sync — sync not required.');

            return Command::SUCCESS;
        }

        $this->info("To sync: {$pendingBefore} products (chunk {$chunk})...");

        $bar = $this->output->createProgressBar($pendingBefore);
        $bar->start();

        $start = microtime(true);
        $result = $sync->execute($chunk, fn (int $count) => $bar->advance($count));

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf(
            'Done in %s s: indexed %d, deleted from index %d, skipped (no document) %d.',
            round(microtime(true) - $start, 2),
            $result->indexed,
            $result->deleted,
            $result->skipped,
        ));

        if ($result->skipped > 0) {
            $this->warn('Skipped products remain with pending_es_sync until data is fixed (brand, media, etc.).');
        }

        if (!$result->isSuccessful()) {
            $this->error("Bulk errors: {$result->bulkErrors}");
            foreach ($result->firstErrors as $err) {
                $this->line(json_encode($err, JSON_UNESCAPED_UNICODE));
            }

            return Command::FAILURE;
        }

        $pendingAfter = $sync->pendingCount();
        if ($pendingAfter > 0) {
            $this->warn("Still pending_es_sync: {$pendingAfter} (document preparation or bulk errors).");
        }

        return Command::SUCCESS;
    }
}
