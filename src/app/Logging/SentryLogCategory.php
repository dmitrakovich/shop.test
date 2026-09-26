<?php

namespace App\Logging;

use App\Enums\LogCategory;
use Illuminate\Log\Logger;
use Monolog\LogRecord;

class SentryLogCategory
{
    /**
     * Tag Sentry logs so Explore can filter one category: log_category:jobs
     */
    public function __invoke(Logger $logger, string $category): void
    {
        $category = LogCategory::from($category)->value;

        $logger->pushProcessor(static function (LogRecord $record) use ($category): LogRecord {
            $record->extra['log_category'] = $category;

            if (array_is_list($record->context) && $record->context !== []) {
                $encoded = json_encode($record->context, JSON_UNESCAPED_UNICODE);

                if (is_string($encoded)) {
                    $record->extra['context'] = $encoded;
                }
            }

            return $record;
        });
    }
}
