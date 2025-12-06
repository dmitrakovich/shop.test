<?php

namespace App\Console\Commands\OneRun;

use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RemoveMediaForOldTrashedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:remove-media-for-old-trashed-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes media files from products that were deleted more than 2 months ago';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $productsQuery = Product::onlyTrashed()->where('deleted_at', '<', now()->subMonths(2));

        $this->output->progressStart($productsQuery->count());

        $productsQuery->each(function (Product $product) {
            $product->media->each(function (Media $media, int $i) {
                if (!$i) { // skip first
                    return;
                }
                $media->delete();
            });
            $this->output->progressAdvance();
        }, 200);

        $this->output->progressFinish();
    }
}
