<?php

namespace App\Enums;

use App\Logging\SentryLogCategory;

enum LogCategory: string
{
    case Jobs = 'jobs';
    case Feeds = 'feeds';
    case Debug = 'debug';
    case UpdateAvailability = 'update_availability';

    /**
     * Sentry Logs channel. Filter in Explore with `log_category:<value>`.
     *
     * @return array{driver: string, name: string, level: string, tap: list<string>}
     */
    public function channel(): array
    {
        return [
            'driver' => 'sentry_logs',
            'name' => $this->value,
            'level' => $this->level(),
            'tap' => [SentryLogCategory::class . ':' . $this->value],
        ];
    }

    /**
     * @return array<string, array{driver: string, name: string, level: string, tap: list<string>}>
     */
    public static function channels(): array
    {
        $channels = [];

        foreach (self::cases() as $category) {
            $channels[$category->value] = $category->channel();
        }

        return $channels;
    }

    private function level(): string
    {
        return match ($this) {
            self::Debug => (string)env('LOG_LEVEL', 'debug'),
            default => 'debug',
        };
    }
}
