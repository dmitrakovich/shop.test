<?php

namespace App\Jobs\AvailableSizes;

use App\Enums\LogCategory;
use App\Jobs\AbstractJob;
use Illuminate\Support\Facades\Log;

abstract class AbstractAvailableSizesJob extends AbstractJob
{
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    protected function logCategory(): LogCategory
    {
        return LogCategory::UpdateAvailability;
    }

    /**
     * Write message in debug log
     *
     * @param  mixed[]  $context
     */
    protected function debug(string $message, array $context = []): void
    {
        Log::channel(LogCategory::Debug->value)->debug($message, $context);
    }
}
