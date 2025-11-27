<?php

namespace App\Jobs\Media;

use App\Enums\Queue;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob as BasePerformConversionsJob;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PerformConversionsJob extends BasePerformConversionsJob
{
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ConversionCollection $conversions,
        protected Media $media,
        protected bool $onlyMissing = false,
    ) {
        $this->onQueue(Queue::MEDIA);
    }
}
