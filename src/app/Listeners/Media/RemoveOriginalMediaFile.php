<?php

namespace App\Listeners\Media;

use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RemoveOriginalMediaFile
{
    /**
     * Handle the event.
     */
    public function handle(ConversionHasBeenCompletedEvent $event): void
    {
        if ($this->isLastUploadedConversion($event->media)) {
            //
        }
    }

    private function isLastUploadedConversion(Media $media): bool
    {
        if ($media->conversions_disk !== 'media') {
            return false;
        }

        /** @var HasMedia&InteractsWithMedia $model */
        $model = new $media->model_type();
        $model->registerMediaConversions();

        return $media->getGeneratedConversions()->count() === count($model->mediaConversions);
    }
}
