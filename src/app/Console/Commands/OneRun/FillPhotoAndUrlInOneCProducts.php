<?php

namespace App\Console\Commands\OneRun;

use App\Models\OneC\Product as ProductFromOneC;
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

        $bar = $this->output->createProgressBar($productsQuery->count());

        $productsQuery
            ->with(['category', 'productFromOneC', 'media'])
            ->select(['id', 'one_c_id', 'slug', 'category_id'])
            ->chunk(200, function (Collection $chunk) use ($bar) {
                $chunk->each(function (Product $product) use ($bar) {
                    /** @var ProductFromOneC $productFromOneC */
                    if ($productFromOneC = $product->productFromOneC) {
                        $productFromOneC->update([
                            'SP6111' => url($product->getUrl()),
                            'SP6116' => $product->getFirstMediaUrl(conversionName: 'catalog'),
                        ]);
                    }
                    $bar->advance();
                });
            });

        $bar->finish();
        $this->output->newLine();
    }
}
