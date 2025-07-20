<?php

use App\Logging\SimpleFormatter;
use App\Logging\TelegramFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'telegram' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\TelegramBotHandler::class,
            'tap' => [TelegramFormatter::class],
            'with' => [
                'apiKey' => env('TELEGRAM_BOT_TOKEN'),
                'channel' => env('TELEGRAM_BOT_CHAT_ID'),
            ],
        ],

        'telegram-dev' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\TelegramBotHandler::class,
            'tap' => [TelegramFormatter::class],
            'with' => [
                'parseMode' => 'MarkdownV2',
                'apiKey' => env('TELEGRAM_DEV_BOT_TOKEN'),
                'channel' => env('TELEGRAM_DEV_BOT_CHAT_ID'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'jobs' => [
            'driver' => 'single',
            'path' => storage_path('logs/jobs.log'),
            'tap' => [SimpleFormatter::class],
            'level' => 'debug',
        ],

        'update_availability' => [
            'driver' => 'single',
            'path' => storage_path('logs/update_availability.log'),
            'tap' => [SimpleFormatter::class],
            'level' => 'debug',
        ],

        'sentry' => [
            'driver' => 'sentry',
        ],

        'feeds' => [
            'driver' => 'single',
            'path' => storage_path('logs/feeds.log'),
            'tap' => [SimpleFormatter::class],
            'level' => 'debug',
        ],

        'debug' => [
            'driver' => 'daily',
            'path' => storage_path('logs/debug.log'),
            'tap' => [SimpleFormatter::class],
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

    ],

];
