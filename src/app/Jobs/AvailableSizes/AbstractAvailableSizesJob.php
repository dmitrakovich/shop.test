<?php

namespace App\Jobs\AvailableSizes;

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

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Write message in logs
     */
    protected function log(string $message, string $level = 'info', string $channel = ''): void
    {
        parent::log($message, $level, 'update_availability');
    }

    /**
     * Write message in debug log
     */
    protected function debug(string $message, array $context = []): void
    {
        Log::channel('debug')->debug($message, $context);
    }
}
