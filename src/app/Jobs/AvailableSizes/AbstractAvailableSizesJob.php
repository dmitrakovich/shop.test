<?php

namespace App\Jobs\AvailableSizes;

use App\Jobs\AbstractJob;

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
    protected function log(string $message, string $level = 'info'): void
    {
        $this->debug($message, 'update_availability', $level);
    }
}
