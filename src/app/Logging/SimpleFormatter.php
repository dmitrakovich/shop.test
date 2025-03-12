<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class SimpleFormatter
{
    /**
     * Формат сообщения
     */
    private string $format = "[%datetime%] %channel%.%level_name%: %message% %context%\n"; //  %extra%

    /**
     * Формат даты
     */
    private string $dateFormat = 'Y-m-d H:i:s'; // .u

    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter($this->format, $this->dateFormat)
            );
        }
    }
}
