<?php

namespace App\Listeners\Media;

use App\Enums\Ads\BannerMediaCollection;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ConvertVideo implements ShouldQueue
{
    private const array VIDEO_COLLECTIONS = [
        'videos',
        BannerMediaCollection::DESKTOP_VIDEO->value,
        BannerMediaCollection::MOBILE_VIDEO->value,
    ];

    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        if (!in_array($event->media->collection_name, self::VIDEO_COLLECTIONS, true)) {
            return;
        }

        if (str_ends_with(strtolower($event->media->file_name), '.mp4')) {
            return;
        }

        $ffmpeg = FFMpeg::create();
        $format = app(\FFMpeg\Format\Video\X264::class);
        $path = $event->media->getPath();
        $resultPath = substr_replace($path, 'mp4', strrpos($path, '.') + 1);
        $video = $ffmpeg->open($path);
        $video->save($format, $resultPath);
        $event->media->update([
            'file_name' => basename($resultPath),
        ]);
    }
}
