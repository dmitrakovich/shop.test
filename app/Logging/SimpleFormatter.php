<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class SimpleFormatter
{
    /**
     * Формат сообщения
     */
    protected string $format = "[%datetime%] %level_name% > %message% %context%\n"; //  %extra%
    /**
     * Формат даты
     */
    protected string $dateFormat = 'Y-m-d H:i:s.u';

    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter($this->format, $this->dateFormat)
            );
        }
    }
}
