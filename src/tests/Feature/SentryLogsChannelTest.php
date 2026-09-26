<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class SentryLogsChannelTest extends TestCase
{
    public function test_sentry_logs_channel_uses_structured_logs_driver(): void
    {
        $channel = config('logging.channels.sentry_logs');

        $this->assertIsArray($channel);
        $this->assertSame('sentry_logs', $channel['driver']);
        $this->assertSame(
            env('SENTRY_LOG_LEVEL', 'warning'),
            $channel['level'],
        );
        $this->assertInstanceOf(LoggerInterface::class, Log::channel('sentry_logs'));
    }

    public function test_sentry_release_comes_from_deployer_release_file(): void
    {
        $path = str_replace('releases/', '', base_path('../.dep/latest_release'));

        $this->assertSame(trim((string)file_get_contents($path)), config('sentry.release'));
    }

    public function test_queue_job_transactions_stay_off_unless_explicitly_enabled(): void
    {
        if (env('SENTRY_TRACE_QUEUE_ENABLED') !== null) {
            $this->markTestSkipped('SENTRY_TRACE_QUEUE_ENABLED is set in the environment.');
        }

        $this->assertFalse(config('sentry.tracing.queue_job_transactions'));
    }
}
