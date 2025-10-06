<?php

namespace App\Console\Commands\OneRun;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RemoveFullConversionMediaFromProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:remove-full-conversion-media-from-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ''; // !!!

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $mediaQuery = Media::query()->where('model_type', Product::class);
        $this->output->progressStart($mediaQuery->count());

        $mediaQuery->each(function (Media $media) {
            Storage::disk($media->disk)->delete($media->getPathRelativeToRoot('full'));

            $generatedConversions = $media->generated_conversions;
            unset($generatedConversions['full']);
            $media->update(['generated_conversions' => $generatedConversions]);

            $this->output->progressAdvance();
        }, 200);
    }
}
