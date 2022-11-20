<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class SimpleFormatter
{
    /**
     * Формат сообщения
     */
    protected $format = "[%datetime%] %channel%.%level_name%: %message% %context%\n"; //  %extra%

    /**
     * Формат даты
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter($this->format, $this->dateFormat)
            );
        }
    }
}
