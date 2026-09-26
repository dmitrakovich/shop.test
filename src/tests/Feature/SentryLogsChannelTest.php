<?php

namespace Tests\Feature;

use App\Enums\LogCategory;
use Illuminate\Support\Facades\Log;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class SentryLogsChannelTest extends TestCase
{
    public function test_sentry_logs_channel_uses_structured_logs_driver(): void
    {
        $channel = config('logging.channels.sentry_logs');

        $this->assertIsArray($channel);
        $this->assertSame('sentry_logs', $channel['driver']);
        $this->assertInstanceOf(Level::class, Level::fromName($channel['level']));
        $this->assertInstanceOf(LoggerInterface::class, Log::channel('sentry_logs'));
    }

    public function test_category_channels_are_sentry_logs(): void
    {
        foreach (LogCategory::cases() as $category) {
            $this->assertSame('sentry_logs', config("logging.channels.{$category->value}.driver"));
            $this->assertInstanceOf(Level::class, Level::fromName(config("logging.channels.{$category->value}.level")));
            $this->assertInstanceOf(LoggerInterface::class, Log::channel($category->value));
        }
    }

    public function test_sentry_release_comes_from_deployer_release_file(): void
    {
        $path = str_replace('releases/', '', base_path('../.dep/latest_release'));

        $this->assertSame(trim((string)file_get_contents($path)), config('sentry.release'));
    }

    public function test_queue_job_transactions_are_off_by_default(): void
    {
        $this->assertFalse(config('sentry.tracing.queue_job_transactions'));
    }
}
