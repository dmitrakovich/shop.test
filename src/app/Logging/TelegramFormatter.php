<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class TelegramFormatter
{
    /**
     * Message format
     */
    protected $format = '%message%';

    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter($this->format));
        }
    }
}
