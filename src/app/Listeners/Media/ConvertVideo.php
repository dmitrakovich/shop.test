<?php

namespace App\Listeners\Media;

use FFMpeg\FFMpeg;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ConvertVideo implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $collectionName = $event->media->collection_name ?? null;
        if ($collectionName === 'videos') {
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
}
