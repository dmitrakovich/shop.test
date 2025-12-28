<?php

namespace App\Listeners\Media;

use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RemoveOriginalMediaFile
{
    /**
     * Handle the event.
     */
    public function handle(ConversionHasBeenCompletedEvent $event): void
    {
        if ($this->areAllConversionsGenerated($event->media)) {
            // !!! remove after upload + use local before upload

            // todo: команда для переноса на S3

            // if (file_exists($event->media->getPath())) {
            //     unlink($event->media->getPath());
            // }
        }
    }

    private function areAllConversionsGenerated(Media $media): bool
    {
        // if ($media->conversions_disk !== 'media') {
        //     return false;
        // }

        // /** @var HasMedia $model */
        // $model = new $media->model_type();
        // $model->registerMediaConversions();

        // return $media->getGeneratedConversions()->count() === count($model->mediaConversions);

        // !!! варианта с чата гпт
        $allConversionNames = $media->getMediaConversionNames();
        $generatedConversions = $media->getGeneratedConversions();

        foreach ($allConversionNames as $conversionName) {
            if (!$generatedConversions->get($conversionName, false)) {
                return false;
            }
        }

        return true;
    }
}
