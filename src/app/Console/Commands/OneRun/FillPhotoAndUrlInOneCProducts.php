<?php

namespace App\Console\Commands\OneRun;

use App\Events\Products\ProductUpdated;
use App\Listeners\OneC\UpdateProduct;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FillPhotoAndUrlInOneCProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:fill-photo-and-url-in-one-c-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills in images and links to products in 1C';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productsQuery = Product::withTrashed()->whereNotNull('one_c_id');

        $this->output->progressStart($productsQuery->count());
        $productUpdater = new UpdateProduct();

        $productsQuery
            ->with(['category', 'productFromOneC', 'media', 'manufacturer', 'countryOfOrigin'])
            ->select(['id', 'one_c_id', 'slug', 'category_id', 'manufacturer_id', 'country_of_origin_id'])
            ->chunk(200, function (Collection $chunk) use ($productUpdater) {
                $chunk->each(function (Product $product) use ($productUpdater) {
                    $productUpdater->handle(new ProductUpdated($product));
                    $this->output->progressAdvance();
                });
            });

        $productsQuery->count();
    }
}
