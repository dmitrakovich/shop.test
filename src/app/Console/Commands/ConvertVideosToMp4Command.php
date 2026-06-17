<?php

namespace App\Console\Commands;

use App\Services\Media\VideoConversionService;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Backfill command for videos uploaded before automatic conversion was enabled.
 */
class ConvertVideosToMp4Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:convert-videos-to-mp4
                            {--collection=* : Limit conversion to specific media collections}
                            {--id=* : Convert only the given media IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert non-MP4 video files in media library to MP4';

    /**
     * Execute the console command.
     */
    public function handle(VideoConversionService $videoConversionService): int
    {
        $query = Media::query()
            ->where('mime_type', 'like', 'video/%')
            ->where('file_name', 'not like', '%.mp4');

        $collections = $this->option('collection');
        if ($collections !== []) {
            $query->whereIn('collection_name', $collections);
        }

        $ids = $this->option('id');
        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        $mediaItems = $query->orderBy('id')->get();

        if ($mediaItems->isEmpty()) {
            $this->info('No videos require conversion.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Converting %d video(s) to MP4...', $mediaItems->count()));

        $progressBar = $this->output->createProgressBar($mediaItems->count());
        $progressBar->start();

        $converted = 0;
        $failed = 0;

        foreach ($mediaItems as $media) {
            try {
                if ($videoConversionService->convertToMp4($media)) {
                    $converted++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->newLine();
                $this->error(sprintf('Media #%d failed: %s', $media->id, $exception->getMessage()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info(sprintf('Converted: %d, failed: %d', $converted, $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
