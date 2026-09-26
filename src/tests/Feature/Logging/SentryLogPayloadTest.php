<?php

namespace Tests\Feature\Logging;

use App\Enums\LogCategory;
use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Jobs\Elasticsearch\UpsertCatalogProductJob;
use App\Jobs\Payment\SendInstallmentNoticeJob;
use App\Models\Audit;
use App\Models\Currency;
use App\Models\Feeds\AbstractFeed;
use App\Services\Feeds\CsvService;
use App\Services\Payment\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger as LaravelLogger;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use RuntimeException;
use Tests\TestCase;

class SentryLogPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_logs_send_only_explicit_non_null_context(): void
    {
        Context::add('1C sale order', ['SP6102' => '+375291234567']);
        $logs = $this->captureLogs(LogCategory::Debug);

        Log::channel(LogCategory::Debug->value)->debug('probe', ['order_id' => null, 'product_id' => 7]);

        $record = $this->onlyRecord($logs);
        $this->assertSame(['product_id' => 7], $record->context);
        $this->assertSame(['log_category' => 'debug'], $record->extra);
    }

    public function test_job_logs_carry_job_name_memory_and_count(): void
    {
        $this->mock(InstallmentService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendNotifications')->once()->andReturn(3);
        });
        $logs = $this->captureLogs(LogCategory::Jobs);

        SendInstallmentNoticeJob::dispatchSync();

        $records = $logs->getRecords();
        $this->assertCount(2, $records);
        [$start, $finish] = $records;

        $this->assertSame('Старт', $start->message);
        $this->assertSame('Успешно выполнено', $finish->message);
        $this->assertSame('SendInstallmentNoticeJob', $finish->context['job']);
        $this->assertIsFloat($finish->context['memory_mb']);
        $this->assertSame(3, $finish->context['count']);
        $this->assertSame(['log_category' => 'jobs'], $finish->extra);
    }

    public function test_failed_job_logs_error_with_job_context(): void
    {
        $logs = $this->captureLogs(LogCategory::Jobs);

        (new UpsertCatalogProductJob(42))->failed(new RuntimeException('Elasticsearch недоступен'));

        $record = $this->onlyRecord($logs);
        $this->assertSame(Level::Error, $record->level);
        $this->assertSame('Ошибка выполнения', $record->message);
        $this->assertSame('UpsertCatalogProductJob', $record->context['job']);
        $this->assertSame(42, $record->context['product_id']);
        $this->assertSame('Elasticsearch недоступен', $record->context['error']);
    }

    public function test_availability_jobs_log_to_their_own_category(): void
    {
        $jobs = $this->captureLogs(LogCategory::Jobs);
        $availability = $this->captureLogs(LogCategory::UpdateAvailability);

        app(UpdateAvailabilityJob::class)->failed(new RuntimeException('1С недоступна'));

        $this->assertSame([], $jobs->getRecords());
        $record = $this->onlyRecord($availability);
        $this->assertSame('UpdateAvailabilityJob', $record->context['job']);
        $this->assertSame(['log_category' => 'update_availability'], $record->extra);
    }

    public function test_feed_generation_logs_one_event_with_size_and_duration(): void
    {
        $currency = Currency::query()->firstOrFail();
        $feed = new class extends AbstractFeed
        {
            const FILE_TYPE = 'csv';

            public function getKey(): string
            {
                return 'sentry_log_test';
            }

            public function getPreparedData(): object
            {
                return (object)['headers' => ['id'], 'rows' => [[1], [2]]];
            }
        };
        $path = storage_path('app/xml/sentry_log_test_' . strtolower($currency->code) . '.csv');
        File::ensureDirectoryExists(dirname($path));
        $this->beforeApplicationDestroyed(fn () => File::delete($path));
        $logs = $this->captureLogs(LogCategory::Feeds);

        (new CsvService($feed, $currency))->generate();

        $record = $this->onlyRecord($logs);
        $this->assertSame('Фид сгенерирован', $record->message);
        $this->assertSame('sentry_log_test', $record->context['feed']);
        $this->assertSame('csv', $record->context['format']);
        $this->assertSame($currency->code, $record->context['currency']);
        $this->assertSame(filesize($path), $record->context['size_bytes']);
        $this->assertIsInt($record->context['duration_ms']);
        $this->assertIsFloat($record->context['memory_peak_mb']);
    }

    public function test_cleanup_command_logs_command_name_and_deleted_count(): void
    {
        Audit::query()->create([
            'event' => 'updated',
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 1,
            'old_values' => [],
            'new_values' => [],
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        $logs = $this->captureLogs(LogCategory::Jobs);

        $this->artisan('cleanup:audits')->assertSuccessful();

        $record = $this->onlyRecord($logs);
        $this->assertSame('Удалены устаревшие записи', $record->message);
        $this->assertSame(['command' => 'cleanup:audits', 'count' => 1], $record->context);
    }

    private function captureLogs(LogCategory $category): TestHandler
    {
        $channel = Log::channel($category->value);
        $this->assertInstanceOf(LaravelLogger::class, $channel);

        $logger = $channel->getLogger();
        $this->assertInstanceOf(Logger::class, $logger);

        $handler = new TestHandler();
        $logger->pushHandler($handler);

        return $handler;
    }

    private function onlyRecord(TestHandler $logs): LogRecord
    {
        $records = $logs->getRecords();
        $this->assertCount(1, $records);

        return $records[0];
    }
}
