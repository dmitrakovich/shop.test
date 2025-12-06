<?php

namespace App\Console\Commands\OneRun;

use App\Jobs\Media\PerformConversionsJob;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MoveProductsMediaToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:move-products-media-to-s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves product media files to S3 storage and regenerates conversions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Config::set('media-library.file_namer', \App\Models\Media\FileNamer::class);

        $mediaQuery = Media::query()->where('model_type', Product::class);
        $this->output->progressStart($mediaQuery->count());

        $mediaQuery->each(function (Media $media) {
            if ($media->conversions_disk === 'media') {
                return;
            }

            foreach ($media->getMediaConversionNames() as $conversionName) {
                Storage::disk($media->conversions_disk)->delete($media->getPathRelativeToRoot($conversionName));
            }

            $media->setCustomProperty('moving', 's3');
            $media->update([
                'conversions_disk' => 'media',
                'generated_conversions' => [],
            ]);

            dispatch_sync(new PerformConversionsJob(ConversionCollection::createForMedia($media), $media));

            $media->forgetCustomProperty('moving')->save();

            $this->output->progressAdvance();
        }, 200);

        $this->output->progressFinish();
    }
}
