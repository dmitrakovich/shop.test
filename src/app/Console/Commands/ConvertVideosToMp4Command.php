<?php

namespace App\Console\Commands;

use App\Models\Media\Media;
use App\Services\Media\VideoConversionService;
use Illuminate\Console\Command;

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
    protected $description = 'Convert videos in media library to web-compatible MP4 (H.264 + AAC), including HEVC-in-MP4';

    /**
     * Execute the console command.
     */
    public function handle(VideoConversionService $videoConversionService): int
    {
        // Include .mp4 — HEVC/H.265 inside an MP4 container still needs re-encoding for Chrome.
        $query = Media::query()
            ->where('mime_type', 'like', 'video/%');

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
            $this->info('No videos found.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Checking %d video(s) for conversion to H.264 MP4...', $mediaItems->count()));

        $progressBar = $this->output->createProgressBar($mediaItems->count());
        $progressBar->start();

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($mediaItems as $media) {
            try {
                if ($videoConversionService->convertToMp4($media)) {
                    $converted++;
                } else {
                    $skipped++;
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
        $this->info(sprintf('Converted: %d, skipped (already compatible): %d, failed: %d', $converted, $skipped, $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
