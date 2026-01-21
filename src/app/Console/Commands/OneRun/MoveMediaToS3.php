<?php

namespace App\Console\Commands\OneRun;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MoveMediaToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:move-media-to-s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves media files to S3 storage and regenerates conversions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Config::set('media-library.file_namer', \App\Models\Media\FileNamer::class);

        $mediaQuery = Media::query()->where('disk', '!=', 'media');

        $this->output->progressStart($mediaQuery->count());

        $mediaQuery->eachById(function (Media $media) {
            $model = $media->model()->withTrashed()->first();
            if ($model) {
                $media->move(model: $model, collectionName: $media->collection_name, diskName: 'media');
            } else {
                $media->forceDelete();
            }

            $this->output->progressAdvance();
        }, 200);

        $this->output->progressFinish();
    }
}
