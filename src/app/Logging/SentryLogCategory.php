<?php

namespace App\Logging;

use App\Enums\LogCategory;
use Illuminate\Log\Logger;
use Monolog\LogRecord;

class SentryLogCategory
{
    /**
     * Tag Sentry logs so Explore can filter one category: log_category:jobs
     *
     * Only the explicit log context is sent. Laravel Context is dropped because jobs
     * put raw 1C rows with customer names and phones there.
     */
    public function __invoke(Logger $logger, string $category): void
    {
        $category = LogCategory::from($category)->value;

        $logger->pushProcessor(static fn (LogRecord $record): LogRecord => $record->with(
            context: array_filter($record->context, static fn (mixed $value): bool => $value !== null),
            extra: ['log_category' => $category],
        ));
    }
}
