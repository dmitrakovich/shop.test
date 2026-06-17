<?php

namespace App\Listeners\Media;

use App\Enums\Ads\BannerMediaCollection;
use App\Enums\Queue;
use App\Services\Media\VideoConversionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Queued handler for Spatie MediaHasBeenAddedEvent — runs after each new video upload.
 */
class ConvertVideo implements ShouldQueue
{
    use InteractsWithQueue;

    public int $timeout = 600;

    public int $tries = 2;

    /** Collections that should be normalized to MP4 for browser playback. */
    private const array VIDEO_COLLECTIONS = [
        'videos',
        BannerMediaCollection::DESKTOP_VIDEO->value,
        BannerMediaCollection::MOBILE_VIDEO->value,
    ];

    public function __construct(private readonly VideoConversionService $videoConversionService) {}

    public function viaQueue(): string
    {
        return Queue::Media->value;
    }

    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (!in_array($media->collection_name, self::VIDEO_COLLECTIONS, true)) {
            return;
        }

        $this->videoConversionService->convertToMp4($media);
    }
}
